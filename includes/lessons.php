<?php
// ==============================================================================
// MÓDULO: LECCIONES, TOPICS Y SLIDERS
// ==============================================================================

// ==============================================================================
// SHORTCODE: SLIDER DE LECCIONES (DISEÑO ORIGINAL - NAV SUPERIOR DERECHA)
// ==============================================================================

add_shortcode('lesson_slider', 'gptwp_lesson_slider_shortcode');
function gptwp_lesson_slider_shortcode() {

    // 1. Validaciones básicas
    if (!is_user_logged_in()) return '<p style="text-align:center; color:#fff;">Debes iniciar sesión.</p>';

    // Cargar librerías Swiper (CDN)
    wp_enqueue_style('swiper-css', 'https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css');
    wp_enqueue_script('swiper-js', 'https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js', array(), null, true);

    $user_id = get_current_user_id();

    // 2. Obtener ID del curso (desde Cookie o parámetro)
    if (isset($_COOKIE['gptwp_curso_actual']) && absint($_COOKIE['gptwp_curso_actual']) > 0) {
        $curso_id = absint($_COOKIE['gptwp_curso_actual']);
    } else {
        return "<div class='ldwp-alert'>Selecciona un curso primero.</div>";
    }

    if (!sfwd_lms_has_access($curso_id, $user_id)) return "<div class='ldwp-alert'>No tienes acceso a este curso.</div>";

    $lessons_raw = learndash_get_course_lessons_list($curso_id);
    if (!$lessons_raw) return "<div class='ldwp-alert'>Este curso no tiene lecciones aún.</div>";

    // Verificar módulos activados (Drip content / Acceso específico)
    $enabled_modules = get_user_meta($user_id, 'enabled_modules_' . $curso_id, true);
    $check_custom_access = !empty($enabled_modules) && is_array($enabled_modules);
    if (current_user_can('administrator') || current_user_can('group_leader')) $check_custom_access = false;

    ob_start();

    // 3. Lógica de Agrupación (INTACTA)
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
    foreach ($sliders as $index => $slider) { ?>
        
        <div class="ldwp-slider-wrapper">
            
            <!-- CABECERA: TÍTULO + BOTONES DE NAVEGACIÓN -->
            <div class="slider-header-container">
                <div class="slider-header-title">
                    <?php if (!empty($slider['title'])): ?>
                        <h2 class="slider-module-title"><?php echo esc_html($slider['title']); ?></h2>
                    <?php else: ?>
                        <!-- Espacio vacío si no hay título para mantener alineación -->
                        <span class="slider-spacer"></span> 
                    <?php endif; ?>
                </div>

                <!-- CONTENEDOR DE BOTONES (Ahora aquí arriba) -->
                <div class="slider-nav-controls">
                    <div class="swiper-button-prev ldwp-nav-btn nav-prev-<?php echo $index; ?>"></div>
                    <div class="swiper-button-next ldwp-nav-btn nav-next-<?php echo $index; ?>"></div>
                </div>
            </div>
            
            <div class="swiper lesson-slider slider-<?php echo $index; ?>">
                <div class="swiper-wrapper">
                    <?php foreach ($slider['lessons'] as $lesson):
                        $lesson_id = $lesson->ID;
                        
                        // Imagen: Intentamos obtener la del lesson, si no, fallback
                        $image_url = get_the_post_thumbnail_url($lesson_id, 'full'); 
                        if (!$image_url) $image_url = get_the_post_thumbnail_url($curso_id, 'large') ?: 'https://via.placeholder.com/240x500/333333/ffffff?text=Leccion'; 
                        
                        // Lógica de Bloqueo
                        $is_locked = false;
                        if ($check_custom_access) {
                            if (!in_array('lesson_' . $lesson_id, $enabled_modules)) $is_locked = true;
                        }

                        // Links
                        $topics = learndash_get_topic_list($lesson_id);
                        $link = !empty($topics) ? get_permalink($topics[0]->ID) : get_permalink($lesson_id);
                        $href = $is_locked ? 'javascript:void(0);' : esc_url($link);
                        
                        $card_class = $is_locked ? 'ldwp-card-locked' : 'ldwp-card-unlocked';
                    ?>
                        <!-- Ancho fijo definido en CSS -->
                        <div class="swiper-slide">
                            <a href="<?php echo $href; ?>" class="ldwp-card-slide <?php echo $card_class; ?>">
                                
                                <!-- IMAGEN DE FONDO (Full Height) -->
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
    <?php } ?>

    <style>
    /* =========================================
       ESTILOS ORIGINALES + AJUSTE NAVEGACIÓN
       ========================================= */
    
    .ldwp-slider-wrapper { 
        margin-bottom: 60px; 
        position: relative; 
        padding: 0 5px; 
    }
    
    /* NUEVO: Contenedor Flex para Título y Botones */
    .slider-header-container {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px; /* Espacio entre cabecera y slider */
        padding-right: 10px; /* Margen derecho seguro */
    }

    .slider-header-title {
        flex-grow: 1;
    }

    /* ESTILOS DEL TÍTULO DE MÓDULO (Originales) */
    .slider-module-title { 
        font-size: 1.6rem; 
        font-weight: 700; 
        margin: 0; /* Quitamos margin bottom porque lo maneja el container */
        color: #ffffff; 
        position: relative; 
        padding-left: 15px; 
        text-transform: capitalize;
    }
    .slider-module-title::before { 
        content:''; 
        position: absolute; 
        font:15px !important;
        left: 0; 
        top: 5px; 
        height: 24px; 
        width: 4px; 
        background: #D4AF37; 
        border-radius: 2px; 
    }

    /* NUEVO: Contenedor de botones arriba a la derecha */
    .slider-nav-controls {
        display: flex;
        gap: 15px; /* Espacio entre flechas */
        align-items: center;
    }

    /* Modificación de botones ESTILIZADOS */
    .ldwp-nav-btn { 
        position: static !important; /* Ya no flotan sobre la imagen */
        margin-top: 0 !important;
        width: 45px !important; 
        height: 45px !important; 
        background: #F9B137 !important; /* Fondo solicitado */
        border: none;
        border-radius: 50%; 
        color: #121212 !important; /* Icono solicitado */
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3); /* Sombra suave */
    }
    
    .ldwp-nav-btn::after { 
        font-size: 15px !important; 
        font-weight: 800;
    }

    .ldwp-nav-btn:hover { 
        background: #fff !important; /* Blanco al pasar el mouse para contraste */
        color: #F9B137 !important;
        transform: scale(1.1); 
        box-shadow: 0 6px 15px rgba(0,0,0,0.5);
    }

    /* Deshabilitar botón si no hay más slides */
    .swiper-button-disabled {
        opacity: 0.5;
        background: #777 !important; /* Gris cuando no se puede clicar */
        color: #aaa !important;
        cursor: not-allowed;
        pointer-events: none;
        box-shadow: none;
    }

    /* --- TUS ESTILOS DE TARJETAS ORIGINALES (INTACTOS) --- */
    .lesson-slider .swiper-slide {
        width: 313px !important; 
        height: auto;
        display: flex;
        justify-content: center;
    }

    .ldwp-card-slide {
        display: block; 
        position: relative; 
        text-decoration: none;
        background: #000;
        border-radius: 14px; 
        overflow: hidden;
        box-shadow: 0 12px 30px rgba(0,0,0,0.4);
        transition: all 0.3s ease;
        width: 313px !important;
        height: 573px !important;
    }
    .ldwp-card-slide:hover {
        transform: translateY(-10px) scale(1.02);
        box-shadow: 0 25px 50px rgba(0,0,0,0.5);
        z-index: 2;
    }
    
    .ldwp-slide-thumb { 
        position: absolute;
        top: 0; left: 0;
        width: 100%; 
        height: 100%; 
        z-index: 1;
    }
    .ldwp-slide-thumb img { 
        width: 100%; 
        height: 100%; 
        object-fit: cover; 
        transition: transform 0.6s ease; 
    }
    .ldwp-card-slide:hover .ldwp-slide-thumb img { transform: scale(1.1); }

    .ldwp-card-locked .ldwp-slide-thumb img { filter: grayscale(100%) brightness(0.5); }
    .ldwp-locked-overlay {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.4);
        display: flex; align-items: center; justify-content: center;
        color: #fff; z-index: 2;
        backdrop-filter: blur(2px);
    }
    .ldwp-lock-circle { font-size: 36px; text-shadow: 0 2px 10px rgba(0,0,0,0.5); }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        // Inicializar cada slider independientemente para conectar sus botones específicos
        <?php foreach ($sliders as $index => $slider) { ?>
            new Swiper('.slider-<?php echo $index; ?>', {
                loop: false,
                slidesPerView: 'auto', 
                spaceBetween: 25, 
                navigation: { 
                    nextEl: '.nav-next-<?php echo $index; ?>', 
                    prevEl: '.nav-prev-<?php echo $index; ?>' 
                },
            });
        <?php } ?>
    });
    </script>
    <?php
    return ob_get_clean();
}


/*MOSTRAR LOS TOPICS*/

function mostrar_topics_con_diseno() {
    global $post;

    // Verificamos si estamos en una lección o un topic
    if ( ! is_singular( ['sfwd-lessons', 'sfwd-topic'] ) ) {
        return '';
    }

    // Si estamos en una lección, usamos ese ID
    if ( $post->post_type === 'sfwd-lessons' ) {
        $lesson_id = $post->ID;
    } else {
        // Si estamos en un topic, obtenemos la lección "padre"
        $lesson_id = learndash_get_setting( $post->ID, 'lesson' );
    }

    // Título de la lección
    $lesson_title = get_the_title( $lesson_id );

    // Obtener curso y topics de esa lección
    $course_id = learndash_get_course_id( $lesson_id );
    $topics = learndash_get_topic_list( $lesson_id, $course_id );

    if ( empty( $topics ) ) {
        return '<p>No hay clases asociadas.</p>';
    }

    $current_topic_id = get_queried_object_id();

    ob_start();

    // Título de la lección
    echo '<div class="titulo-leccion">';
    echo '<h2>' . esc_html( $lesson_title ) . '</h2>';
    echo '</div>';

    // Lista de topics
    echo '<div class="contenedor-topics-personalizado">';
    
    foreach ( $topics as $topic ) {
        $is_active = $topic->ID === $current_topic_id ? ' topic-activo' : '';
        $thumbnail = get_the_post_thumbnail( $topic->ID, 'medium', ['class' => 'imagen-topic'] );
        $title = esc_html( get_the_title( $topic->ID ) );
        $author_id = $topic->post_author;
        $author_name = get_the_author_meta( 'display_name', $author_id );
        $permalink = get_permalink( $topic->ID );

        echo '<div class="topic-item' . $is_active . '">';
        echo '<a href="' . esc_url( $permalink ) . '" class="topic-link">';
        echo '<div class="topic-img">' . $thumbnail . '</div>';
        echo '<div class="topic-info">';
        echo '<h3 class="topic-title">' . $title . '</h3>';
        echo '<span class="topic-author">Autor: ' . esc_html( $author_name ) . '</span>';
        echo '</div>';
        echo '</a>';
        echo '</div>';
    }

    echo '</div>';
    return ob_get_clean();
}
add_shortcode( 'topics_leccion', 'mostrar_topics_con_diseno' );


add_filter( 'learndash_completion_redirect', '__return_false' );

/**
 * BOTÓN PERSONALIZADO DE "COMPLETAR CLASE"
 * Marca el topic actual como completado y redirige al siguiente paso o /aula-virtual.
 */
function gptwp_topic_mark_complete_button() {

	if ( ! is_user_logged_in() || ! is_singular('sfwd-topic') ) {
		return '';
	}

	$user_id   = get_current_user_id();
	$topic_id  = get_the_ID();
	$course_id = learndash_get_course_id( $topic_id );

	// Si el tema ya está completado, mostrar aviso
	if ( learndash_is_topic_complete( $user_id, $topic_id, $course_id ) ) {
		return '<p class="tema-completado" style="color:#fff; font-weight:600; text-align:center;">✅ Clase completada</p>';
	}

	// Procesar envío del formulario
	if ( isset($_POST['gptwp_topic_id'], $_POST['gptwp_nonce']) && wp_verify_nonce($_POST['gptwp_nonce'], 'gptwp_mark_topic_complete') ) {

		$topic_id_posted  = intval($_POST['gptwp_topic_id']);
		$course_id_posted = intval($_POST['gptwp_course_id']);

		// ✅ Marcar manualmente el topic como completado (sin redirección)
		ld_update_course_progress( $user_id, $course_id_posted, array(
			'activity_type'   => 'topic',
			'activity_action' => 'completed',
			'post_id'         => $topic_id_posted,
			'activity_status' => true,
		));

		/**
		 * 🧭 Determinar siguiente paso:
		 * 1️⃣ Siguiente topic dentro de la misma lección.
		 * 2️⃣ Primer topic o lección del siguiente bloque.
		 * 3️⃣ /aula-virtual si no hay más.
		 */
		$next_url = '';

		// Obtener lección actual
		$lesson_id = learndash_get_lesson_id( $topic_id_posted );

		// Obtener topics de la lección actual
		$lesson_topics = learndash_get_topic_list( $lesson_id, $course_id_posted );

		// Buscar el topic siguiente dentro de la misma lección
		$found_current = false;
		if ( ! empty( $lesson_topics ) ) {
			foreach ( $lesson_topics as $topic ) {
				if ( $found_current ) {
					$next_url = get_permalink( $topic->ID );
					break;
				}
				if ( $topic->ID === $topic_id_posted ) {
					$found_current = true;
				}
			}
		}

		// Si no hay más topics → buscar siguiente lección
		if ( empty( $next_url ) ) {
			$all_lessons = learndash_get_course_lessons_list( $course_id_posted, $user_id );
			$found_lesson = false;

			foreach ( $all_lessons as $lesson ) {
				if ( $found_lesson ) {
					// Buscar primer topic de la siguiente lección
					$next_topics = learndash_get_topic_list( $lesson['post']->ID, $course_id_posted );
					if ( ! empty( $next_topics ) ) {
						$next_url = get_permalink( $next_topics[0]->ID );
					} else {
						$next_url = get_permalink( $lesson['post']->ID );
					}
					break;
				}
				if ( $lesson['post']->ID === $lesson_id ) {
					$found_lesson = true;
				}
			}
		}

		// Si ya no hay más contenido, ir a /aula-virtual
		if ( empty( $next_url ) ) {
			$next_url = home_url( '/aula-virtual' );
		}

		// 🔄 Redirigir al siguiente paso (sin conflicto con LearnDash)
		echo '<script>window.location.href = "' . esc_url( $next_url ) . '";</script>';
		exit;
	}

	// Mostrar el botón
	ob_start(); ?>
	<form method="post" class="gptwp-complete-topic-form" style="text-align:center;">
		<input type="hidden" name="gptwp_topic_id" value="<?php echo esc_attr($topic_id); ?>">
		<input type="hidden" name="gptwp_course_id" value="<?php echo esc_attr($course_id); ?>">
		<?php wp_nonce_field('gptwp_mark_topic_complete', 'gptwp_nonce'); ?>
		
		<button type="submit" class="gptwp-complete-btn">Completar clase</button>
	</form>

	<style>
		.gptwp-complete-btn {
			background-color: #fff;
			color: #000;
			font-weight: 600;
			font-size: 16px;
			padding: 12px 32px;
			border: none;
			border-radius: 999px;
			cursor: pointer;
			transition: all 0.25s ease;
			box-shadow: 0 0 0 1px rgba(255,255,255,0.15);
			display: inline-block;
		}
		.gptwp-complete-btn:hover {
			transform: scale(1.03);
			background-color: #f5f5f5;
		}
		.gptwp-complete-btn:active {
			transform: scale(0.97);
		}
		.gptwp-complete-btn:focus {
			outline: none;
			box-shadow: 0 0 0 2px #f6b800;
		}
	</style>
	<?php
	return ob_get_clean();
}
add_shortcode('gptwp_topic_mark_complete', 'gptwp_topic_mark_complete_button');


/**
 * Procesar la acción de completar el topic y redirigir al siguiente paso
 */
add_action('init', 'gptwp_handle_topic_completion');
function gptwp_handle_topic_completion() {
	if (
		isset($_POST['gptwp_nonce'], $_POST['gptwp_topic_id'], $_POST['gptwp_course_id']) &&
		wp_verify_nonce($_POST['gptwp_nonce'], 'gptwp_mark_topic_complete')
	) {
		$user_id   = get_current_user_id();
		$topic_id  = absint($_POST['gptwp_topic_id']);
		$course_id = absint($_POST['gptwp_course_id']);

		if ($user_id && $topic_id && $course_id) {
			// Marca como completado
			learndash_process_mark_complete($user_id, $topic_id);

			// Buscar el siguiente paso
			$next_step_id = learndash_next_post_link(null, false, $user_id, $course_id, $topic_id);

			if ( $next_step_id instanceof WP_Post ) {
				wp_redirect( get_permalink($next_step_id->ID) );
			} else {
				// Si no hay siguiente paso, redirigir al curso
				wp_redirect( get_permalink($course_id) );
			}
			exit;
		}
	}
}

// ---------------------------------------------------------
// 1. LÓGICA DE RASTREO (Se mantiene igual)
// ---------------------------------------------------------

function academy_track_user_progress() {
    // Solo rastreamos si el usuario está logueado y no es una vista administrativa
    if ( ! is_user_logged_in() || is_admin() ) {
        return;
    }

    // LearnDash: 'sfwd-lessons' (Lecciones) y 'sfwd-topic' (Temas)
    $post_types_clases = array( 'sfwd-lessons', 'sfwd-topic' ); 

    if ( is_singular( $post_types_clases ) ) {
        global $post;
        $user_id = get_current_user_id();
        
        // Guardamos el ID del post actual
        update_user_meta( $user_id, '_academy_last_viewed_lesson_id', $post->ID );
    }
}
add_action( 'template_redirect', 'academy_track_user_progress' );


// ---------------------------------------------------------
// 2. HELPER: OBTENER ID (Función auxiliar interna)
// ---------------------------------------------------------

function academy_get_last_visited_id() {
    if ( ! is_user_logged_in() ) return false;
    
    $user_id = get_current_user_id();
    $last_id = get_user_meta( $user_id, '_academy_last_viewed_lesson_id', true );

    if ( ! $last_id ) return false;

    // Verificar que el contenido aún existe y está publicado
    if ( get_post_status( $last_id ) !== 'publish' ) return false;

    return $last_id;
}


// ---------------------------------------------------------
// 3. SHORTCODES MODULARES (Para tu diseño en Elementor)
// ---------------------------------------------------------

/**
 * Shortcode: [ultima_clase_titulo]
 * Uso: Pégalo dentro de un widget de "Encabezado" o "Editor de Texto".
 */
function academy_sc_title() {
    $id = academy_get_last_visited_id();
    return $id ? get_the_title( $id ) : 'No hay clases pendientes';
}
add_shortcode( 'ultima_clase_titulo', 'academy_sc_title' );

/**
 * Shortcode: [ultima_clase_link]
 * Uso: Pégalo en el campo "Enlace" (Link) de cualquier botón o imagen en Elementor.
 */
function academy_sc_link() {
    $id = academy_get_last_visited_id();
    return $id ? get_permalink( $id ) : '#';
}
add_shortcode( 'ultima_clase_link', 'academy_sc_link' );

/**
 * Shortcode: [ultima_clase_imagen_url]
 * Uso: Devuelve la URL cruda.
 * Solo útil si tienes Elementor PRO y usas "Etiquetas Dinámicas" en el campo de imagen.
 */
function academy_sc_image_url() {
    $id = academy_get_last_visited_id();
    if ( ! $id ) return '';
    $url = get_the_post_thumbnail_url( $id, 'large' );
    return $url ? $url : ''; 
}
add_shortcode( 'ultima_clase_imagen_url', 'academy_sc_image_url' );

/**
 * Shortcode: [ultima_clase_imagen]
 * Uso: Pégalo en un widget de "Shortcode" o "HTML".
 * Muestra la etiqueta <img> completa.
 */
function academy_sc_image_tag() {
    $id = academy_get_last_visited_id();
    if ( ! $id ) return '';

    $url = get_the_post_thumbnail_url( $id, 'large' );
    if ( ! $url ) return ''; // O pon una imagen por defecto

    // Agregamos width:100% para que se adapte al contenedor de Elementor
    return '<img src="' . esc_url($url) . '" alt="Clase" style="width:100%; height:auto; display:block; border-radius:8px;">';
}
add_shortcode( 'ultima_clase_imagen', 'academy_sc_image_tag' );

/**
 * Shortcode: [ultima_clase_imagen_fondo]
 * Uso: Pégalo en un widget de "Shortcode" o "HTML".
 * Muestra un div con la imagen de fondo (ideal para mantener alturas iguales).
 * Puedes cambiar la altura escribiendo: [ultima_clase_imagen_fondo height="250px"]
 */
function academy_sc_image_bg( $atts ) {
    $atts = shortcode_atts( array(
        'height' => '180px', // Altura por defecto
        'radius' => '10px'
    ), $atts );

    $id = academy_get_last_visited_id();
    if ( ! $id ) return '';

    $url = get_the_post_thumbnail_url( $id, 'large' );
    if ( ! $url ) $url = 'https://via.placeholder.com/800x400?text=Academia'; 

    return sprintf(
        '<div style="width: 100%%; height: %s; background-image: url(\'%s\'); background-size: cover; background-position: center; border-radius: %s;"></div>',
        esc_attr($atts['height']),
        esc_url($url),
        esc_attr($atts['radius'])
    );
}
add_shortcode( 'ultima_clase_imagen_fondo', 'academy_sc_image_bg' );


// ---------------------------------------------------------
// 4. SHORTCODE COMPLETO (Legacy - Opcional)
// ---------------------------------------------------------
// Mantenemos el shortcode original por si acaso.

function academy_render_resume_widget( $atts ) {
    $last_lesson_id = academy_get_last_visited_id();
    if ( ! $last_lesson_id ) return '';

    $titulo = get_the_title( $last_lesson_id );
    $link   = get_permalink( $last_lesson_id );
    $thumb_url = get_the_post_thumbnail_url( $last_lesson_id, 'medium' );
    if ( ! $thumb_url ) $thumb_url = 'https://via.placeholder.com/300x150?text=Curso'; 

    ob_start();
    ?>
    <div style="background: #fff; padding: 15px; border: 1px solid #ddd; border-radius: 8px; display: flex; align-items: center; gap: 15px;">
        <div style="width: 80px; height: 50px; background: url('<?php echo $thumb_url; ?>') center/cover; border-radius: 4px;"></div>
        <div>
            <div style="font-size: 0.8em; text-transform: uppercase; color: #888;">Continuar viendo:</div>
            <div style="font-weight: bold; margin-bottom: 5px;"><?php echo $titulo; ?></div>
            <a href="<?php echo $link; ?>" style="color: #0073e6; text-decoration: none; font-size: 0.9em;">Reanudar &rarr;</a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode( 'mi_ultima_clase', 'academy_render_resume_widget' );
