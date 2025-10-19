<?php
/**
 * Funciones de Filtrado por Grupos según Tipo de Usuario
 * Creado: 18/10/2025
 * 
 * Este archivo contiene funciones reutilizables para filtrar grupos
 * según el tipo de usuario logueado.
 * 
 * Tipos de Usuario:
 * 1 = ADMINISTRADOR (acceso total)
 * 2 = CPSAM O CENTRO VIDA (acceso total)
 * 3 = CONTRATISTA (acceso total)
 * 4 = TÉCNICO CPSAM (solo grupos CPSAM, Otro, Contratista)
 * 5 = TÉCNICO CENTRO VIDA (solo grupos CV, Otro, Contratista)
 * 7 = SIN ACCESO
 */

/**
 * Obtiene los IDs de grupos permitidos para el usuario actual
 * 
 * @param mysqli $conexion Conexión a la base de datos
 * @param int $tipo_usuario Tipo de usuario (de la sesión)
 * @return array Array de IDs de grupos permitidos (vacío = todos)
 */
function getGruposPermitidos($conexion, $tipo_usuario) {
    // Administradores, CPSAM/CV generales y Contratistas tienen acceso a todos los grupos
    if (in_array($tipo_usuario, [1, 2, 3])) {
        return []; // Array vacío significa "todos los grupos"
    }
    
    $grupos_permitidos = [];
    
    // Técnico CPSAM: grupos que empiezan con "CPSAM", "Otro", "Contratista"
    if ($tipo_usuario == 4) {
        $query = "SELECT id_grupo FROM grupos 
                  WHERE descripcion_grupo LIKE 'CPSAM%' 
                     OR descripcion_grupo LIKE 'Otro%' 
                     OR descripcion_grupo LIKE 'Contratista%'";
        
        $result = $conexion->query($query);
        if ($result) {
            while ($row = $result->fetch_assoc()) {
                $grupos_permitidos[] = $row['id_grupo'];
            }
        }
    }
    
    // Técnico Centro Vida: grupos que empiezan con "CV", "Otro", "Contratista"
    if ($tipo_usuario == 5) {
        $query = "SELECT id_grupo FROM grupos 
                  WHERE descripcion_grupo LIKE 'CV%' 
                     OR descripcion_grupo LIKE 'Otro%' 
                     OR descripcion_grupo LIKE 'Contratista%'";
        
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
 * Obtiene la cláusula WHERE para filtrar por grupos permitidos
 * 
 * @param mysqli $conexion Conexión a la base de datos
 * @param int $tipo_usuario Tipo de usuario
 * @param string $alias_tabla Alias de la tabla que contiene id_grupo (ej: 'p', 'ra', etc.)
 * @return string Cláusula WHERE a agregar a la consulta (con AND al inicio si es necesario)
 */
function getWhereGruposPermitidos($conexion, $tipo_usuario, $alias_tabla = '') {
    $grupos = getGruposPermitidos($conexion, $tipo_usuario);
    
    // Si array vacío = acceso total, no agregar filtro
    if (empty($grupos)) {
        return '';
    }
    
    // Si hay grupos específicos, crear cláusula IN
    if (count($grupos) > 0) {
        $campo_id_grupo = empty($alias_tabla) ? 'id_grupo' : $alias_tabla . '.id_grupo';
        $ids = implode(',', array_map('intval', $grupos));
        return " AND $campo_id_grupo IN ($ids)";
    }
    
    // Si tipo_usuario no tiene grupos (ej: tipo 7), bloquear todo
    $campo_id_grupo = empty($alias_tabla) ? 'id_grupo' : $alias_tabla . '.id_grupo';
    return " AND $campo_id_grupo = -1"; // ID imposible para bloquear acceso
}

/**
 * Filtra un array de opciones de grupos para un select/dropdown
 * 
 * @param mysqli $conexion Conexión a la base de datos
 * @param int $tipo_usuario Tipo de usuario
 * @return array Array de grupos permitidos [id_grupo, descripcion_grupo]
 */
function getGruposParaSelect($conexion, $tipo_usuario) {
    $grupos_permitidos = getGruposPermitidos($conexion, $tipo_usuario);
    
    // Query base
    $query = "SELECT id_grupo, descripcion_grupo FROM grupos WHERE 1=1";
    
    // Si hay restricción, agregar filtro
    if (!empty($grupos_permitidos)) {
        $ids = implode(',', array_map('intval', $grupos_permitidos));
        $query .= " AND id_grupo IN ($ids)";
    } elseif ($tipo_usuario == 7) {
        // Usuario sin acceso
        $query .= " AND id_grupo = -1";
    }
    
    $query .= " ORDER BY descripcion_grupo ASC";
    
    $result = $conexion->query($query);
    $grupos = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $grupos[] = $row;
        }
    }
    
    return $grupos;
}

/**
 * Verifica si un usuario tiene acceso a un grupo específico
 * 
 * @param mysqli $conexion Conexión a la base de datos
 * @param int $tipo_usuario Tipo de usuario
 * @param int $id_grupo ID del grupo a verificar
 * @return bool True si tiene acceso, False si no
 */
function tieneAccesoGrupo($conexion, $tipo_usuario, $id_grupo) {
    $grupos_permitidos = getGruposPermitidos($conexion, $tipo_usuario);
    
    // Si array vacío = acceso total
    if (empty($grupos_permitidos)) {
        return true;
    }
    
    // Verificar si el ID está en los grupos permitidos
    return in_array($id_grupo, $grupos_permitidos);
}

/**
 * Obtiene descripción del tipo de usuario
 * 
 * @param int $tipo_usuario Tipo de usuario
 * @return string Descripción del tipo
 */
function getTipoUsuarioTexto($tipo_usuario) {
    switch($tipo_usuario) {
        case 1: return 'ADMINISTRADOR';
        case 2: return 'CPSAM O CENTRO VIDA';
        case 3: return 'CONTRATISTA';
        case 4: return 'TÉCNICO CPSAM';
        case 5: return 'TÉCNICO CENTRO VIDA';
        case 7: return 'SIN ACCESO';
        default: return 'DESCONOCIDO';
    }
}
?>
