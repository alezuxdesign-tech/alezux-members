<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

class Origin_LMS_Widget_Lesson_Slider extends \Elementor\Widget_Base {

    public function get_name() {
        return 'origin_lms_lesson_slider';
    }

    public function get_title() {
        return esc_html__( 'Slider de Lecciones (Origin LMS)', 'origin-lms' );
    }

    public function get_icon() {
        return 'eicon-slides';
    }

    public function get_categories() {
        return [ 'origin-lms' ];
    }

    public function get_script_depends() {
        return [ 'swiper' ];
    }

    protected function register_controls() {

        // --- CONTENIDO ---
        $this->start_controls_section(
            'section_content',
            [
                'label' => esc_html__( 'Configuración', 'origin-lms' ),
            ]
        );

        $this->add_control(
            'msg_login',
            [
                'label' => esc_html__( 'Mensaje No Logueado', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::TEXTAREA,
                'default' => esc_html__( 'Debes iniciar sesión para ver las lecciones.', 'origin-lms' ),
            ]
        );

        $this->add_control(
            'msg_no_access',
            [
                'label' => esc_html__( 'Mensaje Sin Acceso', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::TEXT,
                'default' => esc_html__( 'No tienes acceso a este curso.', 'origin-lms' ),
            ]
        );

        $this->end_controls_section();

        // --- ESTILO: NAVEGACIÓN ---
        $this->start_controls_section(
            'section_style_nav',
            [
                'label' => esc_html__( 'Navegación (Flechas)', 'origin-lms' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'nav_btn_color',
            [
                'label' => esc_html__( 'Color Fondo', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#F9B137',
                'selectors' => [
                    '{{WRAPPER}} .ldwp-nav-btn' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );
        
        $this->add_control(
            'nav_icon_color',
            [
                'label' => esc_html__( 'Color Ícono', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#121212',
                'selectors' => [
                    '{{WRAPPER}} .ldwp-nav-btn' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'nav_btn_hover_bg',
            [
                'label' => esc_html__( 'Fondo Hover', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .ldwp-nav-btn:hover' => 'background-color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'nav_icon_hover_color',
            [
                'label' => esc_html__( 'Color Ícono Hover', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#F9B137',
                'selectors' => [
                    '{{WRAPPER}} .ldwp-nav-btn:hover' => 'color: {{VALUE}} !important;',
                ],
            ]
        );

        $this->add_control(
            'nav_btn_size',
            [
                'label' => esc_html__( 'Tamaño Botón', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [
                    'px' => [ 'min' => 30, 'max' => 80 ],
                ],
                'default' => [ 'size' => 45 ],
                'selectors' => [
                    '{{WRAPPER}} .ldwp-nav-btn' => 'width: {{SIZE}}px !important; height: {{SIZE}}px !important;',
                ],
            ]
        );

        $this->end_controls_section();

        // --- ESTILO: TARJETA ---
        $this->start_controls_section(
            'section_style_card',
            [
                'label' => esc_html__( 'Tarjeta de Lección', 'origin-lms' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'card_width',
            [
                'label' => esc_html__( 'Ancho Tarjeta', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [ 'px' => [ 'min' => 200, 'max' => 500 ] ],
                'default' => [ 'size' => 313 ],
                'selectors' => [
                    '{{WRAPPER}} .lesson-slider .swiper-slide' => 'width: {{SIZE}}px !important;',
                    '{{WRAPPER}} .ldwp-card-slide' => 'width: {{SIZE}}px !important;',
                ],
            ]
        );

         $this->add_control(
            'card_height',
            [
                'label' => esc_html__( 'Alto Tarjeta', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::SLIDER,
                'range' => [ 'px' => [ 'min' => 300, 'max' => 800 ] ],
                'default' => [ 'size' => 573 ],
                'selectors' => [
                    '{{WRAPPER}} .ldwp-card-slide' => 'height: {{SIZE}}px !important;',
                ],
            ]
        );

        $this->add_control(
            'card_border_radius',
            [
                'label' => esc_html__( 'Radio Borde', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px' ],
                'selectors' => [
                    '{{WRAPPER}} .ldwp-card-slide' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
                'default' => [ 'top' => 14, 'right' => 14, 'bottom' => 14, 'left' => 14, 'isLinked' => true ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'card_shadow',
                'selector' => '{{WRAPPER}} .ldwp-card-slide',
            ]
        );

        $this->end_controls_section();
        
        // --- ESTILO: TÍTULO MÓDULO ---
        $this->start_controls_section(
            'section_style_title',
            [
                'label' => esc_html__( 'Título del Módulo', 'origin-lms' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'mod_title_color',
            [
                'label' => esc_html__( 'Color Título', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#ffffff',
                'selectors' => [
                    '{{WRAPPER}} .slider-module-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'mod_title_typo',
                'selector' => '{{WRAPPER}} .slider-module-title',
            ]
        );

        $this->add_control(
            'mod_bar_color',
            [
                'label' => esc_html__( 'Color Barra Lateral', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'default' => '#D4AF37',
                'selectors' => [
                    '{{WRAPPER}} .slider-module-title::before' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        // --- ESTILO: MENSAJES ---
        $this->start_controls_section(
            'section_style_messages',
            [
                'label' => esc_html__( 'Mensajes / Alertas', 'origin-lms' ),
                'tab' => \Elementor\Controls_Manager::TAB_STYLE,
            ]
        );
        
        $this->add_control(
            'msg_text_color',
            [
                'label' => esc_html__( 'Color Texto', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ldwp-alert' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'msg_bg_color',
            [
                'label' => esc_html__( 'Color Fondo', 'origin-lms' ),
                'type' => \Elementor\Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ldwp-alert' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            \Elementor\Group_Control_Typography::get_type(),
            [
                'name' => 'msg_typography',
                'selector' => '{{WRAPPER}} .ldwp-alert',
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        if (!is_user_logged_in()) {
             echo '<div class="ldwp-alert">' . esc_html($settings['msg_login']) . '</div>';
             return;
        }

        // Enqueue Swiper manually if Elementor hasn't loaded it (redundant check usually)
        if ( ! wp_script_is( 'swiper', 'enqueued' ) ) {
             wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css');
             wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js', array(), null, true);
        }

        $user_id = get_current_user_id();
        $curso_id = 0;

        // 2. Obtener ID del curso (desde Cookie o parámetro)
        if (isset($_COOKIE['gptwp_curso_actual']) && absint($_COOKIE['gptwp_curso_actual']) > 0) {
            $curso_id = absint($_COOKIE['gptwp_curso_actual']);
        } else {
             echo "<div class='ldwp-alert'>Selecciona un curso primero.</div>";
             return;
        }

        if (function_exists('sfwd_lms_has_access') && !sfwd_lms_has_access($curso_id, $user_id)) {
            echo "<div class='ldwp-alert'>" . esc_html($settings['msg_no_access']) . "</div>";
            return;
        }

        if (function_exists('learndash_get_course_lessons_list')) {
            $lessons_raw = learndash_get_course_lessons_list($curso_id);
        } else {
            $lessons_raw = [];
        }
        
        if (!$lessons_raw) {
            echo "<div class='ldwp-alert'>Este curso no tiene lecciones aún.</div>";
            return;
        }

        // Verificar módulos activados (Drip content / Acceso específico)
        $enabled_modules = get_user_meta($user_id, 'enabled_modules_' . $curso_id, true);
        $check_custom_access = !empty($enabled_modules) && is_array($enabled_modules);
        if (current_user_can('administrator') || current_user_can('group_leader')) $check_custom_access = false;

        // 3. Lógica de Agrupación
        $sliders = [];
        $current_group = [];
        $current_title = null;

        foreach ($lessons_raw as $lesson_data) {
            $lesson = $lesson_data['post'];
            $title_raw = trim($lesson->post_title);

            // Detectar separadores
            if (stripos($title_raw, 'separador') === 0) {
                if (!empty($current_group)) {
                    $sliders[] = ['lessons' => $current_group, 'title' => $current_title];
                    $current_group = [];
                }
                $custom_title = null;
                if (preg_match('/\(Titulo:\s*(.*?)\)/i', $title_raw, $matches)) $custom_title = $matches[1];
                $current_title = $custom_title ?: null;
                continue;
            }
            $current_group[] = $lesson;
        }
        if (!empty($current_group)) $sliders[] = ['lessons' => $current_group, 'title' => $current_title];

        // 4. Renderizado
        foreach ($sliders as $index => $slider) { 
            // Unique ID for this slider instance to avoid conflicts if multiple widgets are on page (though standard use is one)
            $slider_uid = $this->get_id() . '_' . $index;
            ?>
            
            <div class="ldwp-slider-wrapper">
                
                <div class="slider-header-container">
                    <div class="slider-header-title">
                        <?php if (!empty($slider['title'])): ?>
                            <h2 class="slider-module-title"><?php echo esc_html($slider['title']); ?></h2>
                        <?php else: ?>
                            <span class="slider-spacer"></span> 
                        <?php endif; ?>
                    </div>

                    <div class="slider-nav-controls">
                        <div class="swiper-button-prev ldwp-nav-btn nav-prev-<?php echo esc_attr($slider_uid); ?>"></div>
                        <div class="swiper-button-next ldwp-nav-btn nav-next-<?php echo esc_attr($slider_uid); ?>"></div>
                    </div>
                </div>
                
                <div class="swiper lesson-slider slider-<?php echo esc_attr($slider_uid); ?>">
                    <div class="swiper-wrapper">
                        <?php foreach ($slider['lessons'] as $lesson):
                            $lesson_id = $lesson->ID;
                            
                            $image_url = get_the_post_thumbnail_url($lesson_id, 'full'); 
                            if (!$image_url) $image_url = get_the_post_thumbnail_url($curso_id, 'large') ?: 'https://via.placeholder.com/240x500/333333/ffffff?text=Leccion'; 
                            
                            $is_locked = false;
                            if ($check_custom_access) {
                                if (!in_array('lesson_' . $lesson_id, $enabled_modules)) $is_locked = true;
                            }

                            $topics = function_exists('learndash_get_topic_list') ? learndash_get_topic_list($lesson_id) : [];
                            $link = !empty($topics) ? get_permalink($topics[0]->ID) : get_permalink($lesson_id);
                            $href = $is_locked ? 'javascript:void(0);' : esc_url($link);
                            
                            $card_class = $is_locked ? 'ldwp-card-locked' : 'ldwp-card-unlocked';
                        ?>
                            <div class="swiper-slide">
                                <a href="<?php echo $href; ?>" class="ldwp-card-slide <?php echo $card_class; ?>">
                                    <div class="ldwp-slide-thumb">
                                        <img src="<?php echo esc_url($image_url); ?>" alt="<?php echo esc_attr(get_the_title($lesson_id)); ?>" />
                                        <?php if ($is_locked): ?>
                                            <div class="ldwp-locked-overlay">
                                                <div class="ldwp-lock-circle">🔒</div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <script>
            document.addEventListener('DOMContentLoaded', function () {
                new Swiper('.slider-<?php echo esc_js($slider_uid); ?>', {
                    loop: false,
                    slidesPerView: 'auto', 
                    spaceBetween: 25, 
                    navigation: { 
                        nextEl: '.nav-next-<?php echo esc_js($slider_uid); ?>', 
                        prevEl: '.nav-prev-<?php echo esc_js($slider_uid); ?>' 
                    },
                });
            });
            </script>

        <?php } ?>

        <style>
        /* CORE STYLES that are not controlled by Elementor selectors */
        .ldwp-slider-wrapper { margin-bottom: 60px; position: relative; padding: 0 5px; }
        .slider-header-container { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; padding-right: 10px; }
        .slider-header-title { flex-grow: 1; }
        .slider-module-title { font-size: 1.6rem; font-weight: 700; margin: 0; position: relative; padding-left: 15px; text-transform: capitalize; }
        .slider-module-title::before { content:''; position: absolute; left: 0; top: 5px; height: 24px; width: 4px; border-radius: 2px; } /* Color via control */
        
        .slider-nav-controls { display: flex; gap: 15px; align-items: center; }
        .ldwp-nav-btn { 
            position: static !important; margin-top: 0 !important;
            border: none; border-radius: 50%; 
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        }
        .ldwp-nav-btn::after { font-size: 15px !important; font-weight: 800; }
        .ldwp-nav-btn:hover { transform: scale(1.1); box-shadow: 0 6px 15px rgba(0,0,0,0.5); }
        .swiper-button-disabled { opacity: 0.5; background: #777 !important; color: #aaa !important; cursor: not-allowed; pointer-events: none; box-shadow: none; }

        .lesson-slider .swiper-slide { display: flex; justify-content: center; }
        .ldwp-card-slide { 
            display: block; position: relative; text-decoration: none;
            background: #000; overflow: hidden;
            box-shadow: 0 12px 30px rgba(0,0,0,0.4);
            transition: all 0.3s ease;
        }
        .ldwp-card-slide:hover { transform: translateY(-10px) scale(1.02); z-index: 2; }
        .ldwp-slide-thumb { position: absolute; top: 0; left: 0; width: 100%; height: 100%; z-index: 1; }
        .ldwp-slide-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.6s ease; }
        .ldwp-card-slide:hover .ldwp-slide-thumb img { transform: scale(1.1); }
        .ldwp-card-locked .ldwp-slide-thumb img { filter: grayscale(100%) brightness(0.5); }
        .ldwp-locked-overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.4); display: flex; align-items: center; justify-content: center; color: #fff; z-index: 2; backdrop-filter: blur(2px); }
        .ldwp-lock-circle { font-size: 36px; text-shadow: 0 2px 10px rgba(0,0,0,0.5); }
        .ldwp-alert { text-align: center; color: #fff; padding: 20px; background: rgba(255,255,255,0.1); border-radius: 8px; }
        </style>
        <?php
    }
}
