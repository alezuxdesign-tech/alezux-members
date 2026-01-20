<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Origin_LMS_Widget_Course_List extends \Elementor\Widget_Base {

    public function get_name() {
        return 'origin_lms_course_list';
    }

    public function get_title() {
        return esc_html__( 'Lista de Cursos (Origin LMS)', 'origin-lms' );
    }

    public function get_icon() {
        return 'eicon-gallery-grid';
    }

    public function get_categories() {
        return [ 'origin-lms' ];
    }

    protected function register_controls() {

        // --- SECCIÓN DE CONTENIDO ---
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__( 'Contenido', 'origin-lms' ),
                'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'btn_text_continue',
            [
                'label' => esc_html__( 'Texto Botón: Continuar', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Continuar', 'origin-lms' ),
                'placeholder' => esc_html__( 'Ej: Continuar', 'origin-lms' ),
            ]
        );

        $this->add_control(
            'btn_text_view_more',
            [
                'label' => esc_html__( 'Texto Botón: Ver más', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Ver más', 'origin-lms' ),
                'placeholder' => esc_html__( 'Ej: Ver más', 'origin-lms' ),
            ]
        );
        
        $this->add_control(
            'no_courses_msg',
            [
                'label' => esc_html__( 'Mensaje Sin Cursos', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__( 'No hay cursos disponibles actualmente.', 'origin-lms' ),
            ]
        );

        $this->add_control(
            'txt_badge_enrolled',
            [
                'label' => esc_html__( 'Texto Badge: En Curso', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'En Curso', 'origin-lms' ),
            ]
        );

        $this->add_control(
            'txt_badge_sales',
            [
                'label' => esc_html__( 'Texto Badge: Venta', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Premium', 'origin-lms' ),
            ]
        );

        $this->add_control(
            'txt_mentors_label',
            [
                'label' => esc_html__( 'Etiqueta Mentores', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Mentores', 'origin-lms' ),
            ]
        );

        $this->add_control(
            'txt_exclusive',
            [
                'label' => esc_html__( 'Texto: Contenido Exclusivo', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'Contenido exclusivo', 'origin-lms' ),
            ]
        );

        $this->end_controls_section();

        // --- SECCIÓN DE ESTILO: TARJETA ---
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
                'label' => esc_html__( 'Color de Fondo', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .ldwp-card' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Border::get_type(),
            [
                'name' => 'card_border',
                'label' => esc_html__( 'Borde', 'origin-lms' ),
                'selector' => '{{WRAPPER}} .ldwp-card',
            ]
        );

        $this->add_control(
            'card_border_radius',
            [
                'label' => esc_html__( 'Radio del Borde', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .ldwp-card' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 16, 'right' => 16, 'bottom' => 16, 'left' => 16,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'card_box_shadow',
                'label' => esc_html__( 'Sombra', 'origin-lms' ),
                'selector' => '{{WRAPPER}} .ldwp-card',
            ]
        );
        
        $this->add_control(
            'card_padding',
            [
                'label' => esc_html__( 'Relleno (Padding)', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .ldwp-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 25, 'right' => 25, 'bottom' => 25, 'left' => 25,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
            ]
        );

        $this->end_controls_section();

        // --- SECCIÓN DE ESTILO: TIPOGRAFÍA ---
        $this->start_controls_section(
            'section_style_typography',
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
                'default' => '#1a1a1a',
                'selectors' => [
                    '{{WRAPPER}} .ldwp-titulo' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'title_typography',
                'label' => esc_html__( 'Tipografía Título', 'origin-lms' ),
                'selector' => '{{WRAPPER}} .ldwp-titulo',
            ]
        );

        $this->end_controls_section();

        // --- SECCIÓN DE ESTILO: DETAILS (Badges, Mentors, Meta) ---
        $this->start_controls_section(
            'section_style_details',
            [
                'label' => esc_html__( 'Detalles (Badges, Mentors, Textos)', 'origin-lms' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'details_heading_badges',
            [
                'label' => esc_html__( 'Badges / Etiquetas', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::HEADING,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'badge_typography',
                'selector' => '{{WRAPPER}} .ldwp-badge',
            ]
        );

        $this->add_control(
            'details_heading_mentors',
            [
                'label' => esc_html__( 'Mentores', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

         $this->add_control(
            'mentor_label_color',
            [
                'label' => esc_html__( 'Color Etiqueta', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .ldwp-label-mini' => 'color: {{VALUE}};' ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'mentor_label_typo',
                'selector' => '{{WRAPPER}} .ldwp-label-mini',
            ]
        );

        $this->add_control(
            'mentor_names_color',
            [
                'label' => esc_html__( 'Color Nombres', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [ '{{WRAPPER}} .ldwp-names-list' => 'color: {{VALUE}};' ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'mentor_names_typo',
                'selector' => '{{WRAPPER}} .ldwp-names-list',
            ]
        );

        $this->add_control(
            'details_heading_meta',
            [
                'label' => esc_html__( 'Textos Meta (Progreso/Info)', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'meta_text_color',
            [
                'label' => esc_html__( 'Color Texto', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [ 
                    '{{WRAPPER}} .ldwp-progress-text' => 'color: {{VALUE}};',
                    '{{WRAPPER}} .ldwp-desc-short' => 'color: {{VALUE}};' 
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'meta_text_typo',
                'selector' => '{{WRAPPER}} .ldwp-progress-text, {{WRAPPER}} .ldwp-desc-short',
            ]
        );

        $this->end_controls_section();

        // --- SECCIÓN DE ESTILO: BOTONES ---
        $this->start_controls_section(
            'section_style_buttons',
            [
                'label' => esc_html__( 'Botones', 'origin-lms' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'btn_typography',
                'selector' => '{{WRAPPER}} .ldwp-btn',
            ]
        );

        // Pestañas Normal / Hover
        $this->start_controls_tabs( 'tabs_buttons' );

        // Normal
        $this->start_controls_tab(
            'tab_button_normal',
            [
                'label' => esc_html__( 'Normal', 'origin-lms' ),
            ]
        );

        $this->add_control(
            'btn_primary_bg',
            [
                'label' => esc_html__( 'Fondo (Principal)', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#1a1a1a',
                'selectors' => [
                    '{{WRAPPER}} .ldwp-btn-primary' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
        $this->add_control(
            'btn_secondary_bg',
            [
                'label' => esc_html__( 'Fondo (Secundario)', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#D4AF37',
                'selectors' => [
                    '{{WRAPPER}} .ldwp-btn-secondary' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'btn_text_color',
            [
                'label' => esc_html__( 'Color Texto', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .ldwp-btn' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        // Hover
        $this->start_controls_tab(
            'tab_button_hover',
            [
                'label' => esc_html__( 'Al Pasar Cursor', 'origin-lms' ),
            ]
        );

        $this->add_control(
            'btn_primary_hover_bg',
            [
                'label' => esc_html__( 'Fondo Hover (Principal)', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#000000',
                'selectors' => [
                    '{{WRAPPER}} .ldwp-btn-primary:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        
         $this->add_control(
            'btn_secondary_hover_bg',
            [
                'label' => esc_html__( 'Fondo Hover (Secundario)', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#c5a02e',
                'selectors' => [
                    '{{WRAPPER}} .ldwp-btn-secondary:hover' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();
        
        $this->add_control(
            'btn_border_radius',
            [
                'label' => esc_html__( 'Radio del Borde', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors' => [
                    '{{WRAPPER}} .ldwp-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [
                    'top' => 100, 'right' => 100, 'bottom' => 100, 'left' => 100,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'separator' => 'before',
            ]
        );

        $this->end_controls_section();
        
        // --- SECCIÓN DE ESTILO: GRID ---
        $this->start_controls_section(
            'section_style_grid',
            [
                'label' => esc_html__( 'Grid (Diseño)', 'origin-lms' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_responsive_control(
            'grid_columns',
            [
                'label' => esc_html__( 'Columnas', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::NUMBER,
                'min' => 1,
                'max' => 6,
                'devices' => [ 'desktop', 'tablet', 'mobile' ],
                'desktop_default' => 3,
                'tablet_default' => 2,
                'mobile_default' => 1,
                'selectors' => [
                    '{{WRAPPER}} .ldwp-grid' => 'grid-template-columns: repeat({{VALUE}}, 1fr);',
                ],
            ]
        );
        
        $this->add_control(
            'grid_gap',
            [
                'label' => esc_html__( 'Espacio entre Elementos', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 30,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ldwp-grid' => 'gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        $cursos = get_posts([
            'post_type'      => 'sfwd-courses',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'orderby'        => 'title',
            'order'          => 'ASC'
        ]);

        if ( ! $cursos ) {
            echo '<p style="text-align:center; color:#666;">' . esc_html( $settings['no_courses_msg'] ) . '</p>';
            return;
        }

        echo '<div class="ldwp-grid">';

        foreach($cursos as $curso):
            $curso_id   = $curso->ID;
            $usuario_id = get_current_user_id();
            $url        = get_permalink($curso_id);
            $tiene_acceso = sfwd_lms_has_access($curso_id, $usuario_id);

            if ($tiene_acceso) {
                $badge = esc_html( $settings['txt_badge_enrolled'] );
                $badge_class = 'ldwp-badge-enrolled';
                $btn_text = $settings['btn_text_continue']; 
                $btn_class = 'ldwp-btn-primary'; 
                $btn_link = $url; 
            } else {
                $badge = esc_html( $settings['txt_badge_sales'] ); 
                $badge_class = 'ldwp-badge-sales';
                $btn_text = $settings['btn_text_view_more']; 
                $btn_class = 'ldwp-btn-secondary'; 
                $btn_link = $url; 
            }

            $thumb = get_the_post_thumbnail($curso_id, 'large') ?: '<div class="ldwp-placeholder"></div>';
            
            $porcentaje = 0;
            if ($tiene_acceso && function_exists('learndash_course_progress')) {
                $progress = learndash_course_progress(['user_id' => $usuario_id, 'course_id' => $curso_id, 'array' => true]);
                $porcentaje = intval($progress['percentage'] ?? 0);
            }

            // --- 2. LÓGICA MENTORES (ADAPTADA) ---
            $mentores_list = []; 
            if (function_exists('get_field')) {
                $mentores_repeater = get_field('mentores', $curso_id);
                if ($mentores_repeater && is_array($mentores_repeater) && !empty($mentores_repeater)) {
                    foreach ($mentores_repeater as $fila_mentor) {
                        $acf_nombre = $fila_mentor['nombre_del_mentor'] ?? '';
                        $acf_foto   = $fila_mentor['portada_mentor'] ?? '';
                        
                        if (!empty($acf_nombre)) {
                            $this_avatar = '';
                            $img_url = '';
                            if ($acf_foto) {
                                if (is_array($acf_foto) && isset($acf_foto['sizes']['thumbnail'])) {
                                    $img_url = $acf_foto['sizes']['thumbnail'];
                                } elseif (is_array($acf_foto) && isset($acf_foto['url'])) {
                                    $img_url = $acf_foto['url'];
                                } elseif (is_numeric($acf_foto)) {
                                    $img = wp_get_attachment_image_src($acf_foto, [80, 80]);
                                    if($img) $img_url = $img[0];
                                } elseif (is_string($acf_foto)) {
                                    $img_url = $acf_foto;
                                }
                            }
                            if ($img_url) {
                                $this_avatar = '<img src="'.esc_url($img_url).'" alt="'.esc_attr($acf_nombre).'" class="ldwp-avatar-img">';
                            } else {
                                $this_avatar = '<div class="ldwp-avatar-initial">'.strtoupper(substr($acf_nombre, 0, 1)).'</div>';
                            }
                            $mentores_list[] = ['name' => $acf_nombre, 'avatar' => $this_avatar];
                        }
                    }
                } 
                // Fallback y author logic omitida para simplificar, se puede añadir si es crítico
            }

            // --- 3. GENERAR HTML MENTORES ---
            $mentores_html = '';
            if (!empty($mentores_list)) {
                $avatars_html = '';
                $nombres_array = [];
                foreach ($mentores_list as $index => $mentor) {
                    if ($index < 4) {
                        $avatars_html .= '<div class="ldwp-avatar-item" style="z-index:'.(10-$index).'">'.$mentor['avatar'].'</div>';
                    }
                    $nombres_array[] = $mentor['name'];
                }
                if (count($mentores_list) > 4) {
                    $restantes = count($mentores_list) - 4;
                    $avatars_html .= '<div class="ldwp-avatar-item ldwp-avatar-more" style="z-index:0">+'. $restantes .'</div>';
                }
                $nombres_str = implode(', ', $nombres_array);
                if (strlen($nombres_str) > 50) {
                    $nombres_str = substr($nombres_str, 0, 47) . '...';
                }

                $mentores_html = '<div class="ldwp-mentores-wrapper">
                                    <div class="ldwp-mentores-stack">'.$avatars_html.'</div>
                                    <div class="ldwp-mentores-text">
                                        <span class="ldwp-label-mini">'.esc_html($settings['txt_mentors_label']).'</span>
                                        <span class="ldwp-names-list">'.esc_html($nombres_str).'</span>
                                    </div>
                                  </div>';
            }
            ?>

            <div class="ldwp-card <?php echo $tiene_acceso ? 'access-granted' : 'access-denied'; ?>">
                <div class="ldwp-thumb">
                    <span class="ldwp-badge <?php echo $badge_class; ?>"><?php echo $badge; ?></span>
                    <?php echo $thumb; ?>
                    <div class="ldwp-thumb-overlay"></div>
                </div>

                <div class="ldwp-content">
                    <h3 class="ldwp-titulo"><?php echo esc_html($curso->post_title); ?></h3>

                    <?php echo $mentores_html; ?>

                    <?php if ($tiene_acceso): ?>
                        <div class="ldwp-progress-wrapper">
                            <div class="ldwp-progress-bar" style="width: <?php echo $porcentaje; ?>%;"></div>
                        </div>
                        <p class="ldwp-progress-text"><?php echo $porcentaje; ?>% completado</p>
                    <?php else: ?>
                        <p class="ldwp-desc-short"><?php echo esc_html($settings['txt_exclusive']); ?></p>
                    <?php endif; ?>

                    <div class="ldwp-footer">
                        <a href="<?php echo esc_url($btn_link); ?>" 
                           class="ldwp-btn <?php echo $btn_class; ?> ldwp-ingresar-curso"
                           data-curso="<?php echo esc_attr($curso_id); ?>">
                           <?php echo esc_html($btn_text); ?>
                        </a>
                    </div>
                </div>
            </div>

        <?php endforeach; ?>
        </div>
        
        <!-- ESTILOS BÁSICOS (ESTRUCTURA) - Los colores vienen de Elementor -->
        <style>
            .ldwp-grid { display: grid; /* Grid columns managed by Elementor */ }
            
            .ldwp-card { 
                /* Background, Border, Shadow managed by Elementor */
                display: flex; flex-direction: column; overflow: hidden; position: relative;
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            .ldwp-card:hover { transform: translateY(-8px); }

            .ldwp-thumb { position: relative; height: 180px; overflow: hidden; background: #f0f0f0; }
            .ldwp-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.5s ease; }
            .ldwp-card:hover .ldwp-thumb img { transform: scale(1.05); }

            .ldwp-badge { 
                position: absolute; top: 15px; left: 15px; padding: 6px 14px; border-radius: 30px; 
                font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
                z-index: 2; box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            }
            .ldwp-badge-enrolled { background: #ffffff; color: #111; } 
            .ldwp-badge-sales { background: #111; color: #D4AF37; }    

            .ldwp-content { /* Padding managed by Elementor */ flex-grow: 1; display: flex; flex-direction: column; }
            .ldwp-titulo { margin: 0 0 15px; font-weight: 600; line-height: 1.4; }
            .ldwp-desc-short { font-size: 0.85rem; color: #888; margin-bottom: 20px; }

            /* MENTORES & FACEPILE */
            .ldwp-mentores-wrapper { display: flex; align-items: center; margin-bottom: 20px; }
            .ldwp-mentores-stack { display: flex; align-items: center; margin-right: 12px; }
            .ldwp-avatar-item { width: 36px; height: 36px; border-radius: 50%; border: 2px solid #fff; overflow: hidden; margin-left: -12px; background: #eee; position: relative; }
            .ldwp-avatar-item:first-child { margin-left: 0; }
            .ldwp-avatar-img { width: 100%; height: 100%; object-fit: cover; }
            .ldwp-avatar-initial, .ldwp-avatar-more { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 12px; color: #fff; background: #111; }
            .ldwp-avatar-more { background: #ccc; color: #333; font-size: 10px; }
            .ldwp-mentores-text { display: flex; flex-direction: column; justify-content: center; overflow: hidden; }
            .ldwp-label-mini { font-size: 0.65rem; color: #999; text-transform: uppercase; font-weight: 700; line-height: 1; margin-bottom: 3px; }
            .ldwp-names-list { font-size: 0.85rem; color: #444; font-weight: 500; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%; }

            /* PROGRESS */
            .ldwp-progress-wrapper { background: #f1f1f1; height: 4px; border-radius: 2px; overflow: hidden; margin-bottom: 8px; width: 100%; }
            .ldwp-progress-bar { background: #111; height: 100%; border-radius: 2px; }
            .ldwp-progress-text { font-size: 0.75rem; color: #999; margin: 0 0 20px; font-weight: 500; text-align: right; }

            /* FOOTER */
            .ldwp-footer { margin-top: auto; }
            .ldwp-btn { display: block; text-align: center; padding: 14px; text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: all 0.3s; }
            /* Colores de botones manejados por Elementor */
        </style>
        <?php
    }
}
