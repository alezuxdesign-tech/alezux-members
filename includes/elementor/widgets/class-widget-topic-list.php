<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Origin_LMS_Widget_Topic_List extends \Elementor\Widget_Base {

    public function get_name() {
        return 'origin_lms_topic_list';
    }

    public function get_title() {
        return esc_html__( 'Lista de Clases (Topics)', 'origin-lms' );
    }

    public function get_icon() {
        return 'eicon-bullet-list';
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
            'msg_no_topics',
            [
                'label' => esc_html__( 'Mensaje Sin Clases', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'No hay clases asociadas.', 'origin-lms' ),
            ]
        );

        $this->end_controls_section();

        // --- ESTILO: LISTA ---
        $this->start_controls_section(
            'section_style_list',
            [
                'label' => esc_html__( 'Estilo de Lista', 'origin-lms' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        // Pestañas Normal / Activo
        $this->start_controls_tabs( 'tabs_items' );

        // Normal
        $this->start_controls_tab(
            'tab_item_normal',
            [
                'label' => esc_html__( 'Normal', 'origin-lms' ),
            ]
        );

        $this->add_control(
            'item_bg_color',
            [
                'label' => esc_html__( 'Fondo Item', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => 'rgba(255,255,255,0.05)',
                'selectors' => [
                    '{{WRAPPER}} .topic-item' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'item_text_color',
            [
                'label' => esc_html__( 'Color Texto Título', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .topic-title' => 'color: {{VALUE}};',
                ],
            ]
        );

         $this->add_control(
            'item_author_color',
            [
                'label' => esc_html__( 'Color Texto Autor', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .topic-author' => 'color: {{VALUE}} !important;',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'topic_title_typo',
                'label' => esc_html__( 'Tipografía Título', 'origin-lms' ),
                'selector' => '{{WRAPPER}} .topic-title',
            ]
        );

         $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'topic_author_typo',
                'label' => esc_html__( 'Tipografía Autor', 'origin-lms' ),
                'selector' => '{{WRAPPER}} .topic-author',
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'item_border',
                'label' => esc_html__( 'Borde', 'origin-lms' ),
                'selector' => '{{WRAPPER}} .topic-item',
            ]
        );
        
        $this->end_controls_tab();

        // Activo
        $this->start_controls_tab(
            'tab_item_active',
            [
                'label' => esc_html__( 'Activo', 'origin-lms' ),
            ]
        );

        $this->add_control(
            'item_active_bg_color',
            [
                'label' => esc_html__( 'Fondo Activo', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => 'rgba(212, 175, 55, 0.15)',
                'selectors' => [
                    '{{WRAPPER}} .topic-item.topic-activo' => 'background-color: {{VALUE}};',
                ],
            ]
        );

         $this->add_control(
            'item_active_text_color',
            [
                'label' => esc_html__( 'Color Texto Activo', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#D4AF37',
                'selectors' => [
                    '{{WRAPPER}} .topic-item.topic-activo .topic-title' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .topic-item.topic-activo .topic-author' => 'color: {{VALUE}}; opacity: 0.9;',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'item_active_border',
                'label' => esc_html__( 'Borde Activo', 'origin-lms' ),
                'selector' => '{{WRAPPER}} .topic-item.topic-activo',
                'defaults' => [
                    'border' => 'solid',
                    'width' => [
                        'top' => 1,
                        'right' => 1,
                        'bottom' => 1,
                        'left' => 1,
                    ],
                    'color' => '#D4AF37',
                ],
            ]
        );

        $this->end_controls_tab();
        $this->end_controls_tabs();

        $this->add_control(
            'item_border_radius',
            [
                'label' => esc_html__( 'Radio Borde', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],
                'selectors' => [
                    '{{WRAPPER}} .topic-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [ 'top' => 12, 'right' => 12, 'bottom' => 12, 'left' => 12, 'isLinked' => true ],
                'separator' => 'before',
            ]
        );

        $this->add_control(
             'item_spacing',
            [
                'label' => esc_html__( 'Espacio entre Items', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'default' => [ 'size' => 15 ],
                'selectors' => [
                    '{{WRAPPER}} .topic-item' => 'margin-bottom: {{SIZE}}px;',
                ],
            ]
        );

        $this->end_controls_section();
        
        // --- ESTILO: IMAGEN ---
        $this->start_controls_section(
            'section_style_image',
            [
                'label' => esc_html__( 'Imagen (Thumbnail)', 'origin-lms' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'image_size',
            [
                'label' => esc_html__( 'Tamaño', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [ 'px' => [ 'min' => 40, 'max' => 150 ] ],
                'default' => [ 'size' => 80 ],
                'selectors' => [
                    '{{WRAPPER}} .topic-img' => 'width: {{SIZE}}px; height: {{SIZE}}px; flex: 0 0 {{SIZE}}px;',
                ],
            ]
        );
        
        $this->add_control(
            'image_radius',
            [
                'label' => esc_html__( 'Radio Imagen', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .topic-img' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [ 'top' => 8, 'right' => 8, 'bottom' => 8, 'left' => 8, 'isLinked' => true ],
            ]
        );

        $this->end_controls_section();
        
        // --- ESTILO: TÍTULO SECCIÓN ---
        $this->start_controls_section(
            'section_style_header',
            [
                'label' => esc_html__( 'Título Sección', 'origin-lms' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'header_color',
            [
                'label' => esc_html__( 'Color', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .titulo-leccion h2' => 'color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'header_typo',
                'label' => esc_html__( 'Tipografía', 'origin-lms' ),
                'selector' => '{{WRAPPER}} .titulo-leccion h2',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        global $post;
        $settings = $this->get_settings_for_display();

        if ( ! is_singular( ['sfwd-lessons', 'sfwd-topic'] ) ) {
            if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
                echo '<div class="ldwp-alert">Este widget solo se muestra en Lecciones o Topics.</div>';
            }
            return;
        }

        if ( $post->post_type === 'sfwd-lessons' ) {
            $lesson_id = $post->ID;
        } else {
            $lesson_id = learndash_get_setting( $post->ID, 'lesson' );
        }

        $lesson_title = get_the_title( $lesson_id );
        $course_id = learndash_get_course_id( $lesson_id );
        
        // Use LearnDash function if available, fallback to empty array
        $topics = function_exists('learndash_get_topic_list') ? learndash_get_topic_list( $lesson_id, $course_id ) : [];

        if ( empty( $topics ) ) {
            echo '<p>' . esc_html( $settings['msg_no_topics'] ) . '</p>';
            return;
        }

        $current_topic_id = get_queried_object_id();
        
        echo '<div class="titulo-leccion">';
        echo '<h2>' . esc_html( $lesson_title ) . '</h2>';
        echo '</div>';

        echo '<div class="contenedor-topics-personalizado">';
        
        foreach ( $topics as $topic ) {
            // Updated class name to match shortcode: topic-activo
            $is_active = $topic->ID === $current_topic_id ? ' topic-activo' : '';
            // Updated image class to match shortcode: imagen-topic
            $thumbnail = get_the_post_thumbnail( $topic->ID, 'medium', ['class' => 'imagen-topic'] );
            
            $title = esc_html( get_the_title( $topic->ID ) );
            $author_id = $topic->post_author;
            $author_name = get_the_author_meta( 'display_name', $author_id );
            $permalink = get_permalink( $topic->ID );

            echo '<div class="topic-item' . $is_active . '">';
            echo '<a href="' . esc_url( $permalink ) . '" class="topic-link">';
            
            if ( ! empty( $thumbnail ) ) {
                echo '<div class="topic-img">' . $thumbnail . '</div>';
            }
            
            echo '<div class="topic-info">';
            echo '<h3 class="topic-title">' . $title . '</h3>';
            echo '<span class="topic-author">Autor: ' . esc_html( $author_name ) . '</span>';
            echo '</div>';
            echo '</a>';
            echo '</div>';
        }

        echo '</div>';
        
        // Removed inline styles to inherit global styles like the shortcode
    }
}
