<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Clase principal para la integración con Elementor.
 */
class Origin_LMS_Elementor {

    /**
     * Instancia única (Singleton)
     */
    private static $_instance = null;

    /**
     * Obtener instancia
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'plugins_loaded', [ $this, 'init' ] );
    }

    /**
     * Inicializar
     */
    public function init() {
        // Verificar si Elementor está instalado y activo
        if ( ! did_action( 'elementor/loaded' ) ) {
            return;
        }

        // Registrar categoría de widgets
        add_action( 'elementor/elements/categories_registered', [ $this, 'register_categories' ] );

        // Registrar widgets
        add_action( 'elementor/widgets/register', [ $this, 'register_widgets' ] );
        
        // Cargar scripts y estilos del editor si es necesario
        add_action( 'elementor/editor/after_enqueue_styles', [ $this, 'enqueue_editor_styles' ] );
    }

    /**
     * Registrar categoría personalizada "Origin LMS"
     */
    public function register_categories( $elements_manager ) {
        $elements_manager->add_category(
            'origin-lms',
            [
                'title' => esc_html__( 'Alezux Members', 'alezux-members' ),
                'icon'  => 'fa fa-graduation-cap',
            ]
        );
    }

    /**
     * Registrar los Widgets
     */
    public function register_widgets( $widgets_manager ) {
        // Incluir archivos de widgets aquí
        require_once( __DIR__ . '/widgets/class-widget-course-list.php' );
        require_once( __DIR__ . '/widgets/class-widget-my-courses.php' );
        require_once( __DIR__ . '/widgets/class-widget-lesson-slider.php' );
        require_once( __DIR__ . '/widgets/class-widget-topic-list.php' );
        require_once( __DIR__ . '/widgets/class-widget-complete-button.php' );

        // Registrar las clases de los widgets
        $widgets_manager->register( new \Origin_LMS_Widget_Course_List() );
        $widgets_manager->register( new \Origin_LMS_Widget_My_Courses() );
        $widgets_manager->register( new \Origin_LMS_Widget_Lesson_Slider() );
        $widgets_manager->register( new \Origin_LMS_Widget_Topic_List() );
        $widgets_manager->register( new \Origin_LMS_Widget_Complete_Button() );
    }
    
    /**
     * Estilos para el editor (opcional, para que se vea bien en el panel)
     */
    public function enqueue_editor_styles() {
        // Aquí podríamos encolar CSS específico para los controles del editor si fuera necesario
    }
}

// Inicializar
Origin_LMS_Elementor::instance();
