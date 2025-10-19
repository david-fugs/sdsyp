<?php
/**
 * Librería de Filtros de Grupos por Tipo de Usuario
 * 
 * Este archivo contiene funciones reutilizables para filtrar grupos
 * según el tipo de usuario en sesión.
 * 
 * Tipos de Usuario:
 * - 1: ADMIN (acceso completo)
 * - 2: CPSAM/CV (acceso completo)
 * - 3: CONTRATISTA (acceso completo)
 * - 4: TÉCNICO CPSAM (solo grupos CPSAM%, Otro%, Contratista%)
 * - 5: TÉCNICO CENTRO VIDA (solo grupos CV%, Otro%, Contratista%)
 * - 7: SIN ACCESO (acceso completo)
 * 
 * @author Sistema SDSYP
 * @version 1.0
 * @date 2025-10-18
 */

/**
 * Obtiene los IDs de grupos permitidos según el tipo de usuario
 * 
 * @param mysqli $conexion Conexión a la base de datos
 * @param int|null $tipo_usuario Tipo de usuario desde $_SESSION
 * @return array Array de IDs de grupos permitidos (vacío = todos permitidos)
 */
function getGruposPermitidos($conexion, $tipo_usuario) {
    // Si no hay tipo de usuario o es admin/contratista/etc, permitir todos
    if (!$tipo_usuario || in_array($tipo_usuario, [1, 2, 3, 7])) {
        return []; // Array vacío significa "todos los grupos"
    }
    
    $grupos_permitidos = [];
    
    // Tipo 4: TÉCNICO CPSAM
    if ($tipo_usuario == 4) {
        $query = "SELECT id_grupo FROM grupos 
                  WHERE descripcion_grupo LIKE 'CPSAM %' 
                     OR descripcion_grupo = 'CPSAM'
                     OR descripcion_grupo LIKE 'Otro %'
                     OR descripcion_grupo = 'Otro'
                     OR descripcion_grupo LIKE 'Contratista %'
                     OR descripcion_grupo = 'Contratista'
                  ORDER BY descripcion_grupo ASC";
        
        $result = $conexion->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $grupos_permitidos[] = $row['id_grupo'];
            }
        }
    }
    
    // Tipo 5: TÉCNICO CENTRO VIDA
    if ($tipo_usuario == 5) {
        $query = "SELECT id_grupo FROM grupos 
                  WHERE descripcion_grupo LIKE 'CV %' 
                     OR descripcion_grupo = 'CV'
                     OR descripcion_grupo LIKE 'Otro %'
                     OR descripcion_grupo = 'Otro'
                     OR descripcion_grupo LIKE 'Contratista %'
                     OR descripcion_grupo = 'Contratista'
                  ORDER BY descripcion_grupo ASC";
        
        $result = $conexion->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $grupos_permitidos[] = $row['id_grupo'];
            }
        }
    }
    
    return $grupos_permitidos;
}

/**
 * Genera cláusula WHERE para filtrar consultas SQL por grupos permitidos
 * 
 * @param mysqli $conexion Conexión a la base de datos
 * @param int|null $tipo_usuario Tipo de usuario desde $_SESSION
 * @param string $alias_tabla Alias de la tabla en la consulta SQL (ej: 'p', 'g', 'ra')
 * @return string Cláusula WHERE para agregar a la consulta (incluye AND al inicio)
 */
function getWhereGruposPermitidos($conexion, $tipo_usuario, $alias_tabla = 'p') {
    $grupos_permitidos = getGruposPermitidos($conexion, $tipo_usuario);
    
    // Si está vacío, significa que puede ver todos los grupos
    if (empty($grupos_permitidos)) {
        return '';
    }
    
    // Generar lista de IDs para la cláusula IN
    $ids_string = implode(',', array_map('intval', $grupos_permitidos));
    
    // Retornar cláusula WHERE con el alias de tabla correcto
    return " AND {$alias_tabla}.id_grupo IN ({$ids_string})";
}

/**
 * Obtiene array de grupos para usar en dropdowns/selects
 * Filtra según tipo de usuario
 * 
 * @param mysqli $conexion Conexión a la base de datos
 * @param int|null $tipo_usuario Tipo de usuario desde $_SESSION
 * @return array Array asociativo con id_grupo => descripcion_grupo
 */
function getGruposParaSelect($conexion, $tipo_usuario) {
    $grupos = [];
    
    // Construir consulta base
    $query = "SELECT id_grupo, descripcion_grupo FROM grupos";
    
    // Aplicar filtros si es usuario técnico (tipos 4 o 5)
    if ($tipo_usuario == 4) {
        $query .= " WHERE descripcion_grupo LIKE 'CPSAM %' 
                       OR descripcion_grupo = 'CPSAM'
                       OR descripcion_grupo LIKE 'Otro %'
                       OR descripcion_grupo = 'Otro'
                       OR descripcion_grupo LIKE 'Contratista %'
                       OR descripcion_grupo = 'Contratista'";
    } elseif ($tipo_usuario == 5) {
        $query .= " WHERE descripcion_grupo LIKE 'CV %' 
                       OR descripcion_grupo = 'CV'
                       OR descripcion_grupo LIKE 'Otro %'
                       OR descripcion_grupo = 'Otro'
                       OR descripcion_grupo LIKE 'Contratista %'
                       OR descripcion_grupo = 'Contratista'";
    }
    
    $query .= " ORDER BY descripcion_grupo ASC";
    
    $result = $conexion->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $grupos[$row['id_grupo']] = $row['descripcion_grupo'];
        }
    }
    
    return $grupos;
}

/**
 * Verifica si un usuario tiene acceso a un grupo específico
 * 
 * @param mysqli $conexion Conexión a la base de datos
 * @param int|null $tipo_usuario Tipo de usuario desde $_SESSION
 * @param int $id_grupo ID del grupo a verificar
 * @return bool True si tiene acceso, False si no
 */
function tieneAccesoGrupo($conexion, $tipo_usuario, $id_grupo) {
    // Admin y otros tipos tienen acceso completo
    if (!$tipo_usuario || in_array($tipo_usuario, [1, 2, 3, 7])) {
        return true;
    }
    
    $grupos_permitidos = getGruposPermitidos($conexion, $tipo_usuario);
    
    // Si está vacío, tiene acceso a todos
    if (empty($grupos_permitidos)) {
        return true;
    }
    
    // Verificar si el ID está en la lista de permitidos
    return in_array($id_grupo, $grupos_permitidos);
}

/**
 * Obtiene el texto descriptivo de un tipo de usuario
 * 
 * @param int $tipo_usuario Tipo de usuario
 * @return string Descripción del tipo de usuario
 */
function getTipoUsuarioTexto($tipo_usuario) {
    $tipos = [
        1 => 'ADMIN',
        2 => 'CPSAM/CV',
        3 => 'CONTRATISTA',
        4 => 'TÉCNICO CPSAM',
        5 => 'TÉCNICO CENTRO VIDA',
        7 => 'SIN ACCESO'
    ];
    
    return isset($tipos[$tipo_usuario]) ? $tipos[$tipo_usuario] : 'DESCONOCIDO';
}

?>
