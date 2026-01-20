<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Origin_LMS_Widget_My_Courses extends \Elementor\Widget_Base {

    public function get_name() {
        return 'origin_lms_my_courses';
    }

    public function get_title() {
        return esc_html__( 'Mis Cursos (Origin LMS)', 'origin-lms' );
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
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
            'msg_login',
            [
                'label' => esc_html__( 'Mensaje No Logueado', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__( 'Debes iniciar sesión para ver los cursos.', 'origin-lms' ),
            ]
        );

        $this->add_control(
            'msg_no_courses',
            [
                'label' => esc_html__( 'Mensaje Sin Cursos', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__( 'No hay cursos disponibles actualmente.', 'origin-lms' ),
            ]
        );

        $this->add_control(
            'btn_text_continue',
            [
                'label' => esc_html__( 'Botón: Continuar', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'CONTINUAR', 'origin-lms' ),
            ]
        );

        $this->add_control(
            'btn_text_more',
            [
                'label' => esc_html__( 'Botón: Más Info', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'MÁS INFORMACIÓN', 'origin-lms' ),
            ]
        );

        $this->add_control(
            'badge_text_enrolled',
            [
                'label' => esc_html__( 'Badge: En Curso', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'EN CURSO', 'origin-lms' ),
            ]
        );

        $this->add_control(
            'badge_text_available',
            [
                'label' => esc_html__( 'Badge: Disponible', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'DISPONIBLE', 'origin-lms' ),
            ]
        );

        $this->add_control(
            'txt_access_restricted',
            [
                'label' => esc_html__( 'Texto: Acceso Restringido', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'ACCESO RESTRINGIDO', 'origin-lms' ),
            ]
        );

        $this->end_controls_section();

        // --- ESTILO: TARJETA ---
        $this->start_controls_section(
            'section_style_card',
            [
                'label' => esc_html__( 'Tarjeta', 'origin-lms' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'card_bg_color',
            [
                'label' => esc_html__( 'Fondo', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#141414',
                'selectors' => [
                    '{{WRAPPER}} .ldwp-card-fluid' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'card_border_color_hover',
             [
                'label' => esc_html__( 'Color Borde (Hover)', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#F9B137',
                'selectors' => [
                    '{{WRAPPER}} .ldwp-card-fluid:hover' => 'border-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'card_border_radius',
            [
                'label' => esc_html__( 'Radio del Borde', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .ldwp-card-fluid' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 20, 'right' => 20, 'bottom' => 20, 'left' => 20,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
            ]
        );

        $this->end_controls_section();

        // --- ESTILO: TIPOGRAFÍA ---
        $this->start_controls_section(
            'section_style_typo',
            [
                'label' => esc_html__( 'Tipografía', 'origin-lms' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'title_color',
            [
                'label' => esc_html__( 'Color Título', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .ldwp-card-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => esc_html__( 'Tipografía Título', 'origin-lms' ),
                'selector' => '{{WRAPPER}} .ldwp-card-title',
            ]
        );

        $this->add_control(
            'typo_heading_badges',
            [
                'label' => esc_html__( 'Badges / Etiquetas', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'badge_typography',
                'selector' => '{{WRAPPER}} .ldwp-status-badge',
            ]
        );

        $this->add_control(
            'typo_heading_meta',
            [
                'label' => esc_html__( 'Metadatos (Progreso)', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'meta_typography',
                'selector' => '{{WRAPPER}} .ldwp-meta-text',
            ]
        );
        
        $this->add_control(
            'meta_color',
            [
                'label' => esc_html__( 'Color Texto Meta', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .ldwp-meta-text' => 'color: {{VALUE}};' ],
            ]
        );


        $this->add_control(
            'typo_heading_buttons',
            [
                'label' => esc_html__( 'Botones', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'btn_typography',
                'selector' => '{{WRAPPER}} .ldwp-action-btn',
            ]
        );
        
        $this->add_control(
            'typo_heading_msgs',
            [
                'label' => esc_html__( 'Mensajes Globales', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        
        $this->add_control(
            'msg_global_color',
            [
                'label' => esc_html__( 'Color Mensajes', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .ldwp-alert-simple' => 'color: {{VALUE}}; border-color: {{VALUE}};' ],
            ]
        );
        
         $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'msg_global_typo',
                'selector' => '{{WRAPPER}} .ldwp-alert-simple',
            ]
        );

        $this->end_controls_section();

        // --- ESTILO: PROGRESO ---
        $this->start_controls_section(
            'section_style_progress',
            [
                'label' => esc_html__( 'Barra de Progreso', 'origin-lms' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'prog_bar_color',
            [
                'label' => esc_html__( 'Color Barra', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#F9B137',
                'selectors' => [
                    '{{WRAPPER}} .ldwp-prog-bar' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'prog_bg_color',
            [
                'label' => esc_html__( 'Color Fondo Barra', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#222222',
                'selectors' => [
                    '{{WRAPPER}} .ldwp-prog-container' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // --- ESTILO: GRID ---
        $this->start_controls_section(
            'section_style_grid',
            [
                'label' => esc_html__( 'Grid', 'origin-lms' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

         $this->add_responsive_control(
            'grid_min_width',
            [
                'label' => esc_html__( 'Ancho Mínimo Columna', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'default' => 280,
                'selectors' => [
                    '{{WRAPPER}} .ldwp-fluid-grid' => 'grid-template-columns: repeat(auto-fill, minmax({{VALUE}}px, 1fr));',
                ],
            ]
        );

        $this->add_control(
            'grid_gap',
            [
                'label' => esc_html__( 'Espacio (Gap)', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'default' => [
                    'size' => 25,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ldwp-fluid-grid' => 'gap: {{SIZE}}px;',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        if ( ! is_user_logged_in() ) {
            echo '<div class="ldwp-alert-simple">' . esc_html( $settings['msg_login'] ) . '</div>';
            return;
        }

        $usuario_id = get_current_user_id();

        $all_courses = get_posts([
            'post_type'      => 'sfwd-courses',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC'
        ]);

        if ( empty( $all_courses ) ) {
            echo '<div class="ldwp-alert-simple">' . esc_html( $settings['msg_no_courses'] ) . '</div>';
            return;
        }

        echo '<div class="ldwp-fluid-grid">';

        foreach ($all_courses as $curso_post):
            $curso_id = $curso_post->ID;
            $url      = get_permalink($curso_id);
            $thumb    = get_the_post_thumbnail($curso_id, 'large') ?: '<div class="ldwp-placeholder"></div>';
            
            // Verificación de acceso
            $has_access = sfwd_lms_has_access($curso_id, $usuario_id);

            // Auto-reparación de acceso (Legacy logic)
            if (!$has_access) {
                $access_meta = get_user_meta($usuario_id, 'course_' . $curso_id . '_access_from', true);
                $our_mark = get_user_meta($usuario_id, 'enabled_modules_' . $curso_id, true);
                if (!empty($access_meta) || !empty($our_mark)) {
                    if (function_exists('ld_update_course_access')) {
                        ld_update_course_access($usuario_id, $curso_id, false);
                        $has_access = true; 
                    }
                }
            }

            if ($has_access) {
                $badge = esc_html($settings['badge_text_enrolled']);
                $badge_class = 'ldwp-badge-enrolled';
                $btn_text = esc_html($settings['btn_text_continue']);
                $btn_class = 'ldwp-btn-primary';
            } else {
                $badge = esc_html($settings['badge_text_available']);
                $badge_class = 'ldwp-badge-info';
                $btn_text = esc_html($settings['btn_text_more']);
                $btn_class = 'ldwp-btn-info';
            }

            $porcentaje = 0;
            if ($has_access && function_exists('learndash_course_progress')) {
                $progress = learndash_course_progress([
                    'user_id'   => $usuario_id,
                    'course_id' => $curso_id,
                    'array'     => true
                ]);
                $porcentaje = intval($progress['percentage'] ?? 0);
            }

            // Mentores
            $mentores_html = '';
            if (function_exists('get_field')) {
                $mentores_repeater = get_field('mentores', $curso_id);
                if ($mentores_repeater && is_array($mentores_repeater)) {
                    $avatars_html = '';
                    $count = 0;
                    foreach ($mentores_repeater as $fila) {
                        if($count >= 3) break;
                        $img = $fila['portada_mentor'] ?? '';
                        $img_url = '';
                        if (is_array($img) && isset($img['sizes']['thumbnail'])) {
                            $img_url = $img['sizes']['thumbnail'];
                        } elseif (is_string($img)) {
                            $img_url = $img;
                        }
                        
                        if($img_url) $avatars_html .= '<div class="ldwp-avatar-stack-item" style="z-index:'.(10-$count).'"><img src="'.esc_url($img_url).'"></div>';
                        $count++;
                    }
                    if($avatars_html) $mentores_html = '<div class="ldwp-mentores-row">'.$avatars_html.'</div>';
                }
            }
            ?>
            <div class="ldwp-card-fluid">
                <div class="ldwp-thumb-wrapper">
                    <span class="ldwp-status-badge <?php echo $badge_class; ?>"><?php echo $badge; ?></span>
                    <?php echo $thumb; ?>
                    <div class="ldwp-overlay-gradient"></div>
                </div>
                <div class="ldwp-card-body">
                    <h3 class="ldwp-card-title"><?php echo esc_html($curso_post->post_title); ?></h3>
                    <?php echo $mentores_html; ?>
                    
                    <?php if ($has_access): ?>
                        <div class="ldwp-prog-container">
                            <div class="ldwp-prog-bar" style="width: <?php echo $porcentaje; ?>%;"></div>
                        </div>
                        <p class="ldwp-meta-text"><?php echo $porcentaje; ?>% COMPLETADO</p>
                    <?php else: ?>
                        <p class="ldwp-meta-text locked"><?php echo esc_html($settings['txt_access_restricted']); ?></p>
                    <?php endif; ?>

                    <div class="ldwp-card-footer">
                        <a href="<?php echo esc_url($url); ?>" class="ldwp-action-btn <?php echo $btn_class; ?>">
                            <?php echo $btn_text; ?>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
        
        <style>
            /* BASE STYLES - Colors managed by Elementor */
            .ldwp-fluid-grid {
                display: grid;
                gap: 25px;
                width: 100%;
                box-sizing: border-box;
            }
            .ldwp-alert-simple { 
                padding: 30px; text-align: center; background: #141414; 
                color: #888; border: 1px solid #333; width: 100%; border-radius: 12px;
            }

            .ldwp-card-fluid {
                border: 1px solid rgba(255,255,255,0.05);
                overflow: hidden;
                display: flex;
                flex-direction: column;
                transition: transform 0.3s ease, border-color 0.3s ease, box-shadow 0.3s ease;
                box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            }
            .ldwp-card-fluid:hover {
                transform: translateY(-5px);
                box-shadow: 0 15px 40px rgba(0,0,0,0.3);
            }

            .ldwp-thumb-wrapper { position: relative; height: 180px; background: #000; }
            .ldwp-thumb-wrapper img { width: 100%; height: 100%; object-fit: cover; opacity: 0.9; transition: opacity 0.3s; }
            .ldwp-card-fluid:hover .ldwp-thumb-wrapper img { opacity: 1; }
            
            .ldwp-overlay-gradient {
                position: absolute; bottom: 0; left: 0; width: 100%; height: 60%;
                background: linear-gradient(to top, #141414, transparent);
            }

            .ldwp-status-badge {
                position: absolute; top: 12px; left: 12px;
                padding: 6px 12px; font-size: 10px; font-weight: 800;
                text-transform: uppercase; z-index: 2; color: #000; letter-spacing: 0.5px;
                border-radius: 30px;
            }
            .ldwp-badge-enrolled { background: #fff; }
            .ldwp-badge-info { background: #F9B137; }

            .ldwp-card-body { padding: 25px; flex-grow: 1; display: flex; flex-direction: column; }
            .ldwp-card-title { font-size: 17px; font-weight: 700; margin: 0 0 15px 0; line-height: 1.4; }

            .ldwp-mentores-row { display: flex; margin-bottom: 20px; padding-left: 5px; }
            .ldwp-avatar-stack-item {
                width: 32px; height: 32px; border: 2px solid #141414;
                border-radius: 50%; overflow: hidden; margin-left: -10px; background: #333;
                box-shadow: 0 2px 5px rgba(0,0,0,0.5);
            }
            .ldwp-avatar-stack-item:first-child { margin-left: 0; }
            .ldwp-avatar-stack-item img { width: 100%; height: 100%; object-fit: cover; }

            .ldwp-prog-container { height: 6px; width: 100%; margin-bottom: 8px; border-radius: 10px; overflow: hidden; }
            .ldwp-prog-bar { height: 100%; box-shadow: 0 0 10px rgba(249,177,55,0.4); border-radius: 10px; }
            .ldwp-meta-text { font-size: 11px; color: #888; font-weight: 700; letter-spacing: 1px; margin: 0 0 25px 0; }
            .ldwp-meta-text.locked { color: #555; }

            .ldwp-card-footer { margin-top: auto; }
            .ldwp-action-btn {
                display: block; text-align: center; padding: 14px;
                font-size: 13px; font-weight: 800; text-decoration: none;
                text-transform: uppercase; letter-spacing: 1px; border: 1px solid transparent;
                transition: 0.3s; width: 100%; box-sizing: border-box;
                border-radius: 50px;
            }
            .ldwp-btn-primary { background: transparent; border-color: #F9B137; color: #F9B137; }
            .ldwp-btn-primary:hover { background: #F9B137; color: #000; box-shadow: 0 5px 20px rgba(249,177,55,0.3); }
            .ldwp-btn-info { background: #222; color: #ccc; border-color: #333; }
            .ldwp-btn-info:hover { background: #fff; color: #000; border-color: #fff; }
        </style>
        <?php
    }
}
