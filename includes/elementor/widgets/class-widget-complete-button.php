<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Origin_LMS_Widget_Complete_Button extends \Elementor\Widget_Base {

    public function get_name() {
        return 'origin_lms_complete_button';
    }

    public function get_title() {
        return esc_html__( 'Botón Completar Clase (Origin LMS)', 'origin-lms' );
    }

    public function get_icon() {
        return 'eicon-check-circle-o';
    }

    public function get_categories() {
        return [ 'origin-lms' ];
    }

    protected function register_controls() {

        // --- CONTENIDO ---
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__( 'Contenido', 'origin-lms' ),
            ]
        );

        $this->add_control(
            'btn_text',
            [
                'label' => esc_html__( 'Texto Botón', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Completar clase', 'origin-lms' ),
                'placeholder' => esc_html__( 'Ej: Siguiente Clase', 'origin-lms' ),
            ]
        );

        $this->add_control(
            'msg_completed',
            [
                'label' => esc_html__( 'Mensaje Completado', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( '✅ Clase completada', 'origin-lms' ),
            ]
        );
        
        $this->add_responsive_control(
            'align',
            [
                'label' => esc_html__( 'Alineación', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__( 'Izquierda', 'origin-lms' ),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__( 'Centro', 'origin-lms' ),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__( 'Derecha', 'origin-lms' ),
                        'icon' => 'eicon-text-align-right',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '{{WRAPPER}} .gptwp-complete-topic-wrapper' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // --- ESTILO ---
        $this->start_controls_section(
            'section_style_button',
            [
                'label' => esc_html__( 'Estilo Botón', 'origin-lms' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'btn_typography',
                'selector' => '{{WRAPPER}} .gptwp-complete-btn',
            ]
        );

        // Pestañas Normal / Hover
        $this->start_controls_tabs( 'tabs_button_style' );

        // Normal
        $this->start_controls_tab(
            'tab_button_normal',
            [
                'label' => esc_html__( 'Normal', 'origin-lms' ),
            ]
        );

        $this->add_control(
            'btn_text_color',
            [
                'label' => esc_html__( 'Color Texto', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#000000',
                'selectors' => [
                    '{{WRAPPER}} .gptwp-complete-btn' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'btn_bg_color',
            [
                'label' => esc_html__( 'Color Fondo', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .gptwp-complete-btn' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        // Hover
        $this->start_controls_tab(
            'tab_button_hover',
            [
                'label' => esc_html__( 'Cursor (Hover)', 'origin-lms' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'btn_text_color_hover',
            [
                'label' => esc_html__( 'Color Texto (Hover)', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#000000',
                'selectors' => [
                    '{{WRAPPER}} .gptwp-complete-btn:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'btn_bg_color_hover',
            [
                'label' => esc_html__( 'Color Fondo (Hover)', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#f5f5f5',
                'selectors' => [
                    '{{WRAPPER}} .gptwp-complete-btn:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'btn_hover_animation',
            [
                'label' => esc_html__( 'Animación', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::HOVER_ANIMATION,
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->add_control(
            'btn_border_radius',
            [
                'label' => esc_html__( 'Radio Borde', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .gptwp-complete-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [ 'top' => 100, 'right' => 100, 'bottom' => 100, 'left' => 100, 'unit' => 'px', 'isLinked' => true ],
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'btn_padding',
            [
                'label' => esc_html__( 'Relleno', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .gptwp-complete-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [ 'top' => 12, 'right' => 32, 'bottom' => 12, 'left' => 32, 'unit' => 'px', 'isLinked' => true ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'btn_box_shadow',
                'selector' => '{{WRAPPER}} .gptwp-complete-btn',
            ]
        );

        $this->end_controls_section();
        
        // --- ESTILO MENSAJE COMPLETADO ---
         $this->start_controls_section(
            'section_style_msg',
            [
                'label' => esc_html__( 'Estilo Mensaje (Completado)', 'origin-lms' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'msg_color',
            [
                'label' => esc_html__( 'Color Texto', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .tema-completado' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'msg_typography',
                'selector' => '{{WRAPPER}} .tema-completado',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        if ( ! is_user_logged_in() || ! is_singular('sfwd-topic') ) {
             if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                // Show mock button in editor
                echo '<div class="gptwp-complete-topic-wrapper">';
                echo '<button class="gptwp-complete-btn">' . esc_html($settings['btn_text']) . '</button>';
                echo '</div>';
            }
            return;
        }

        $user_id   = get_current_user_id();
        $topic_id  = get_the_ID();
        
        // LearnDash fallback check
        if (!function_exists('learndash_get_course_id')) return;
        
        $course_id = learndash_get_course_id( $topic_id );

        echo '<div class="gptwp-complete-topic-wrapper">';

        // Si el tema ya está completado
        if ( function_exists('learndash_is_topic_complete') && learndash_is_topic_complete( $user_id, $topic_id, $course_id ) ) {
            echo '<p class="tema-completado">' . esc_html($settings['msg_completed']) . '</p>';
        } else {
            // Formulario
            ?>
            <form method="post" class="gptwp-complete-topic-form">
                <input type="hidden" name="gptwp_topic_id" value="<?php echo esc_attr($topic_id); ?>">
                <input type="hidden" name="gptwp_course_id" value="<?php echo esc_attr($course_id); ?>">
                <?php wp_nonce_field('gptwp_mark_topic_complete', 'gptwp_nonce'); ?>
                
                <button type="submit" class="gptwp-complete-btn elementor-animation-<?php echo esc_attr( $settings['btn_hover_animation'] ); ?>">
                    <?php echo esc_html($settings['btn_text']); ?>
                </button>
            </form>
            <?php
        }
        
        echo '</div>';
        
        ?>
        <style>
            .gptwp-complete-btn {
                border: none; cursor: pointer; transition: all 0.25s ease;
                display: inline-block;
            }
            .gptwp-complete-btn:focus { outline: none; }
            .tema-completado { font-weight: 600; margin: 0; }
        </style>
        <?php
    }
}
