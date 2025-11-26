<?php
/**
 * Filtros por Grupo de Usuario
 * 
 * Este archivo contiene funciones reutilizables para aplicar filtros
 * basados en el grupo del usuario (id_grupo). Esto permite que ciertos
 * tipos de usuarios solo vean información relacionada con su grupo específico.
 * 
 * Escalable para agregar más tipos de usuarios con restricciones similares.
 */

/**
 * Verifica si el usuario actual debe tener filtros por grupo
 * 
 * @param int $tipo_usuario Tipo de usuario de la sesión
 * @return bool True si debe aplicar filtros, False si no
 */
function debeAplicarFiltroGrupo($tipo_usuario) {
    // Lista de tipos de usuario que deben tener filtros por grupo
    $tipos_con_filtro = [11]; // 11: INGENIERO CENTRO VIDA
    
    // Agregar más tipos aquí en el futuro si se necesita
    // Ejemplo: $tipos_con_filtro = [11, 12, 13];
    
    return in_array($tipo_usuario, $tipos_con_filtro);
}

/**
 * Obtiene el id_grupo del usuario actual desde la sesión
 * 
 * @return int|null ID del grupo o null si no está definido
 */
function obtenerIdGrupoUsuario() {
    return isset($_SESSION['id_grupo']) ? $_SESSION['id_grupo'] : null;
}

/**
 * Genera la condición WHERE para filtrar por grupo
 * 
 * @param string $alias_tabla Alias de la tabla en la consulta SQL (ej: 'p' para personas, 'g' para grupos)
 * @return string Condición SQL para agregar al WHERE, o string vacío si no aplica
 */
function obtenerCondicionFiltroGrupo($alias_tabla = 'p') {
    if (!isset($_SESSION['tipo_usuario']) || !isset($_SESSION['id_grupo'])) {
        return '';
    }
    
    $tipo_usuario = $_SESSION['tipo_usuario'];
    $id_grupo = $_SESSION['id_grupo'];
    
    if (debeAplicarFiltroGrupo($tipo_usuario) && $id_grupo) {
        return " AND {$alias_tabla}.id_grupo = {$id_grupo}";
    }
    
    return '';
}

/**
 * Modifica una consulta SQL para agregar filtros por grupo si es necesario
 * 
 * @param string $sql Consulta SQL original
 * @param string $alias_tabla Alias de la tabla en la consulta
 * @return string SQL modificado con filtros aplicados
 */
function aplicarFiltroGrupoSQL($sql, $alias_tabla = 'p') {
    $condicion_filtro = obtenerCondicionFiltroGrupo($alias_tabla);
    
    if (empty($condicion_filtro)) {
        return $sql;
    }
    
    // Si ya tiene WHERE, agregar con AND
    if (stripos($sql, 'WHERE') !== false) {
        $sql = str_replace('WHERE', "WHERE 1=1 {$condicion_filtro} AND", $sql);
    } else {
        // Si no tiene WHERE, agregarlo antes del ORDER BY, GROUP BY o LIMIT
        $keywords = ['ORDER BY', 'GROUP BY', 'LIMIT'];
        $inserted = false;
        
        foreach ($keywords as $keyword) {
            if (stripos($sql, $keyword) !== false) {
                $sql = str_ireplace($keyword, "WHERE 1=1 {$condicion_filtro} {$keyword}", $sql);
                $inserted = true;
                break;
            }
        }
        
        // Si no encontró ninguna keyword, agregar al final
        if (!$inserted) {
            $sql .= " WHERE 1=1 {$condicion_filtro}";
        }
    }
    
    return $sql;
}

/**
 * Obtiene información del grupo del usuario actual
 * 
 * @param mysqli $mysqli Conexión a la base de datos
 * @return array|null Array con información del grupo o null si no existe
 */
function obtenerInfoGrupoUsuario($mysqli) {
    $id_grupo = obtenerIdGrupoUsuario();
    
    if (!$id_grupo) {
        return null;
    }
    
    $query = "SELECT * FROM grupos WHERE id_grupo = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("i", $id_grupo);
    $stmt->execute();
    $result = $stmt->get_result();
    $grupo = $result->fetch_assoc();
    $stmt->close();
    
    return $grupo;
}

/**
 * Verifica si el usuario tiene acceso a un registro específico basado en su grupo
 * 
 * @param mysqli $mysqli Conexión a la base de datos
 * @param string $cedula_persona Cédula de la persona a verificar
 * @return bool True si tiene acceso, False si no
 */
function usuarioTieneAccesoAPersona($mysqli, $cedula_persona) {
    if (!isset($_SESSION['tipo_usuario'])) {
        return false;
    }
    
    $tipo_usuario = $_SESSION['tipo_usuario'];
    
    // Si no debe aplicar filtro, tiene acceso a todo
    if (!debeAplicarFiltroGrupo($tipo_usuario)) {
        return true;
    }
    
    $id_grupo_usuario = obtenerIdGrupoUsuario();
    
    if (!$id_grupo_usuario) {
        return false;
    }
    
    // Verificar si la persona pertenece al grupo del usuario
    $query = "SELECT id_grupo FROM personas WHERE cedula_persona = ?";
    $stmt = $mysqli->prepare($query);
    $stmt->bind_param("s", $cedula_persona);
    $stmt->execute();
    $result = $stmt->get_result();
    $persona = $result->fetch_assoc();
    $stmt->close();
    
    if (!$persona) {
        return false;
    }
    
    return $persona['id_grupo'] == $id_grupo_usuario;
}

/**
 * Genera un mensaje informativo sobre el filtro aplicado
 * 
 * @param mysqli $mysqli Conexión a la base de datos
 * @return string HTML con el mensaje informativo o string vacío
 */
function generarMensajeFiltroGrupo($mysqli) {
    if (!isset($_SESSION['tipo_usuario'])) {
        return '';
    }
    
    $tipo_usuario = $_SESSION['tipo_usuario'];
    
    if (!debeAplicarFiltroGrupo($tipo_usuario)) {
        return '';
    }
    
    $grupo = obtenerInfoGrupoUsuario($mysqli);
    
    if (!$grupo) {
        return '<div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> 
                    No tiene un grupo asignado. Contacte al administrador.
                </div>';
    }
    
    return '<div class="alert alert-info">
                <i class="bi bi-info-circle"></i> 
                Está viendo información filtrada para el grupo: <strong>' . htmlspecialchars($grupo['descripcion_grupo']) . '</strong>
            </div>';
}
?>
