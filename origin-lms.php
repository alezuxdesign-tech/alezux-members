<?php
/**
 * Plugin Name: Alezux Members
 * Plugin URI:  https://github.com/alezuxdesign-tech/members
 * Description: Funcionalidades personalizadas modularizadas.
 * Version:     2.0.1
 * Author:      Alezux Design
 * Author URI:  https://alezuxdesign.com
 * License:     GPL v2 or later
 */

// Evitar acceso directo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// DEFINICIÓN DE RUTAS
define( 'GPTWP_PATH', plugin_dir_path( __FILE__ ) );
define( 'GPTWP_URL', plugin_dir_url( __FILE__ ) );

// INCLUSIÓN DE MÓDULOS
// El orden es importante para dependencias (aunque la mayoría son hooks independientes)

// 1. Autenticación y Seguridad
require_once GPTWP_PATH . 'includes/auth.php';

// 2. Funcionalidades de Lecciones y Topics
require_once GPTWP_PATH . 'includes/lessons.php';

// 3. Funcionalidades de Cursos y Listados
require_once GPTWP_PATH . 'includes/courses.php';

// 4. Progreso, Gamificación y Tracking de Video
require_once GPTWP_PATH . 'includes/progress.php';

// 5. Administración, CRM, Finanzas e Importador
require_once GPTWP_PATH . 'includes/admin.php';

// 6. Email Marketing y Comunicaciones
require_once GPTWP_PATH . 'includes/email.php';

// 7. Logros y Notificaciones
require_once GPTWP_PATH . 'includes/achievements.php';

// 8. Integración con Elementor (Widgets)
require_once GPTWP_PATH . 'includes/elementor/class-elementor-loader.php';

// FIN DEL DOCUMENTO
