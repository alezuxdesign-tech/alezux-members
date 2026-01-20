<?php
// ==============================================================================
// MÓDULO: PROGRESO Y GAMIFICACIÓN
// ==============================================================================

/**
 * SHORTCODE: BARRA DE PROGRESO DE LECCIONES (ESTILO TRADERPRO)
 * Uso: [academia_progreso]
 */
function academia_shortcode_progreso_ld() {

    if ( ! is_user_logged_in() ) {
        return '<p style="color:#999;">Debes iniciar sesión para ver tu progreso.</p>';
    }

    $user_id = get_current_user_id();

    // Validar LearnDash
    if ( ! function_exists( 'learndash_course_progress' ) ) {
        return '<p style="color:red;">LearnDash no está activo.</p>';
    }

    // ================================
    // LEER CURSO DESDE LA COOKIE
    // ================================
    $curso_id = isset($_COOKIE['gptwp_curso_actual']) ? absint($_COOKIE['gptwp_curso_actual']) : 0;

    if ( ! $curso_id ) {
        return '<p style="color:#999;">No hay curso activo seleccionado.</p>';
    }

    // Verificar que el curso pertenece al usuario
    $cursos_usuario = learndash_user_get_enrolled_courses($user_id);

    if ( ! in_array($curso_id, $cursos_usuario) ) {
        return '<p style="color:#999;">Este curso no pertenece a tu cuenta.</p>';
    }

    // ================================
    // OBTENER PROGRESO DEL CURSO
    // ================================
    $progress = learndash_course_progress(array(
        'user_id'   => $user_id,
        'course_id' => $curso_id,
        'array'     => true
    ));

    $porcentaje = isset($progress['percentage']) ? intval($progress['percentage']) : 0;

    // ================================
    // ESTILO "TraderPRO Mastery"
    // ================================
    $total_barras = 64;
    $barras_activas = round( $total_barras * $porcentaje / 100 );

    ob_start(); ?>
    
    <div class="academia-progreso-container" style="display:flex; flex-direction:column; gap:8px; width:100%; max-width:509px;">
        <div class="academia-progreso-barras" style="display:flex; gap:3px;">
            <?php for ( $i = 1; $i <= $total_barras; $i++ ) : 
                $color = ( $i <= $barras_activas ) ? '#f6b800' : '#555'; ?>
                <div style="width:5px; height:40px; border-radius:3px; background:<?php echo esc_attr($color); ?>;"></div>
            <?php endfor; ?>
        </div>
        <p style="text-align:right; color:#fff; font-size:14px; margin:0;">
            <?php echo esc_html($porcentaje); ?>% Completado
        </p>
    </div>

    <?php
    return ob_get_clean();
}
add_shortcode( 'academia_progreso', 'academia_shortcode_progreso_ld' );


function academia_shortcode_lecciones_ld() {

    if ( ! is_user_logged_in() ) return '0 / 0 Lecciones';

    $user_id = get_current_user_id();

    // Leer cookie del curso activo
    $curso_id = isset($_COOKIE['gptwp_curso_actual']) ? absint($_COOKIE['gptwp_curso_actual']) : 0;

    if ( ! $curso_id ) {
        return '0 / 0 Lecciones';
    }

    // Verificar que el curso pertenece al usuario
    $cursos_usuario = learndash_user_get_enrolled_courses( $user_id );
    if ( ! in_array($curso_id, $cursos_usuario) ) {
        return '0 / 0 Lecciones';
    }

    // Obtener steps del curso
    $steps = learndash_get_course_steps( $curso_id );
    if ( empty($steps) ) return '0 / 0 Lecciones';

    $total_lecciones = 0;
    $lecciones_completadas = 0;

    foreach ( $steps as $step_id ) {

        // Solo lessons
        if ( get_post_type($step_id) !== 'sfwd-lessons' ) continue;

        $titulo = get_the_title($step_id);

        // Omitir separadores
        if ( stripos($titulo, 'Separador') === 0 ) continue;

        $total_lecciones++;

        // Obtener topics dentro de la lesson
        $topics = learndash_get_topic_list($step_id);

        // Si no tiene topics → revisar la lesson en sí
        if ( empty($topics) ) {
            if ( learndash_is_lesson_complete($user_id, $step_id, $curso_id) ) {
                $lecciones_completadas++;
            }
            continue;
        }

        // Verificar si TODOS los topics están completados
        $topics_completados = 0;

        foreach ( $topics as $topic ) {
            if ( learndash_is_topic_complete($user_id, $topic->ID, $curso_id) ) {
                $topics_completados++;
            }
        }

        // Si todos los topics están completados → lesson completada
        if ( $topics_completados === count($topics) ) {
            $lecciones_completadas++;
        }
    }

    return $lecciones_completadas . ' / ' . $total_lecciones . ' Lecciones';
}
add_shortcode( 'academia_lecciones', 'academia_shortcode_lecciones_ld' );


function shortcode_total_lecciones_usuario_ld() {

    // Usuario no logueado
    if ( ! is_user_logged_in() ) {
        return '<span style="color:#999;">Inicia sesión para ver tus módulos.</span>';
    }

    $user_id = get_current_user_id();

    // Validar LearnDash
    if ( ! function_exists( 'learndash_get_course_steps' ) ) {
        return '<span style="color:red;">LearnDash no está activo.</span>';
    }

    // =======================================
    // LEER CURSO DESDE LA COOKIE
    // =======================================
    $curso_id = isset($_COOKIE['gptwp_curso_actual']) ? absint($_COOKIE['gptwp_curso_actual']) : 0;

    if ( ! $curso_id ) {
        return '<span style="color:#999;">No hay curso activo seleccionado.</span>';
    }

    // Verificar que el curso pertenece al usuario
    $cursos_usuario = learndash_user_get_enrolled_courses( $user_id );
    if ( ! in_array($curso_id, $cursos_usuario) ) {
        return '<span style="color:#999;">Este curso no pertenece a tu cuenta.</span>';
    }

    // =======================================
    // OBTENER LAS LECCIONES DEL CURSO
    // =======================================
    $steps = learndash_get_course_steps( $curso_id );

    if ( empty( $steps ) ) {
        return '<span style="color:#999;">Este curso no tiene módulos disponibles.</span>';
    }

    $total_modulos = 0;

    foreach ( $steps as $step_id ) {

        // Solo contar las LESSONS (no topics)
        if ( get_post_type( $step_id ) !== 'sfwd-lessons' ) {
            continue;
        }

        $titulo = get_the_title( $step_id );

        // OMITIR lecciones que empiezan con "Separador"
        if ( stripos( $titulo, 'Separador' ) === 0 ) {
            continue;
        }

        $total_modulos++;
    }

    // =======================================
    // DEVOLVER RESULTADO
    // =======================================
    return esc_html( $total_modulos ) . ' Módulos';
}
add_shortcode( 'total_lecciones_usuario', 'shortcode_total_lecciones_usuario_ld' );


function shortcode_curso_activo_usuario_ld() {

    if ( ! is_user_logged_in() ) {
        return '<span style="color:#999;">Inicia sesión para ver tu curso activo.</span>';
    }

    $user_id = get_current_user_id();

    // Validar que LearnDash está cargado
    if ( ! function_exists( 'learndash_user_get_enrolled_courses' ) ) {
        return '<span style="color:red;">LearnDash no está activo.</span>';
    }

    // Obtener cursos del usuario
    $cursos_usuario = learndash_user_get_enrolled_courses( $user_id );

    if ( empty( $cursos_usuario ) ) {
        return '<span style="color:#999;">No estás inscrito en ningún curso.</span>';
    }

    // ================================
    // LEER CURSO DESDE LA COOKIE
    // ================================
    $curso_cookie = isset($_COOKIE['gptwp_curso_actual']) ? absint($_COOKIE['gptwp_curso_actual']) : 0;

    // Si no hay cookie → no se puede mostrar curso activo
    if ( ! $curso_cookie ) {
        return '<span style="color:#999;">No hay curso activo seleccionado.</span>';
    }

    // Verificar que ese curso pertenece al usuario
    if ( ! in_array( $curso_cookie, $cursos_usuario ) ) {
        return '<span style="color:#999;">Este curso no pertenece a tu cuenta.</span>';
    }

    // Obtener título del curso
    $titulo = get_the_title( $curso_cookie );

    if ( empty( $titulo ) ) {
        return '<span style="color:#999;">Curso no encontrado.</span>';
    }

    return esc_html( $titulo );
}
add_shortcode( 'curso_activo_usuario', 'shortcode_curso_activo_usuario_ld' );


// DASHBOARD – GAUGE SEGMENTADO PREMIUM (COLOR #F9B137)

function gptwp_premium_course_progress() {

    if ( ! is_user_logged_in() ) return 'Debes iniciar sesión.';

    $user_id = get_current_user_id();
    
    // Verificación de seguridad por si Learndash no está activo
    if (!function_exists('learndash_user_get_enrolled_courses')) {
        return 'Learndash no está activo.';
    }

    $courses = learndash_user_get_enrolled_courses($user_id);

    if ( empty($courses) ) return 'No estás inscrito en ningún curso.';

    $total = 0;
    $course_data = [];

    foreach ( $courses as $course_id ) {

        $progress = learndash_course_progress([
            'user_id'   => $user_id,
            'course_id'=> $course_id,
            'array'    => true
        ]);

        $percentage = isset($progress['percentage']) ? (int) $progress['percentage'] : 0;

        $total += $percentage;

        $course_data[] = [
            'title' => get_the_title($course_id),
            'percent' => $percentage
        ];
    }

    // Evitar división por cero
    $count = count($course_data);
    $average = $count > 0 ? round( $total / $count ) : 0;

    // CONFIGURACIÓN VISUAL
    $segments = 23;
    $radius   = 130;
    $cx       = 160;
    $cy       = 160;
    $active   = round($segments * $average / 100);

    ob_start(); ?>

    <div class="gptwp-premium-gauge">

        <svg viewBox="0 0 320 190" class="gptwp-gauge-svg">

            <?php for ($i = 0; $i < $segments; $i++) :

                $angle = -180 + ($i * (180 / ($segments - 1)));
                $rad = deg2rad($angle);

                $x = $cx + cos($rad) * $radius;
                $y = $cy + sin($rad) * $radius;

                $rotation = $angle + 90;
                $style = '';

                // Lógica de color y GLOW
                if ($i < $active) {
                    $opacity = 0.4 + (0.6 * ($i / $active));
                    $color = 'rgba(249,177,55,' . $opacity . ')';
                    
                    // EDITADO: Reduje el blur a 3px para que el brillo no se mezcle 
                    // entre barras, logrando que se vean individuales.
                    $style = 'filter: drop-shadow(0 0 3px rgba(249,177,55, 0.9));'; 
                } else {
                    $color = '#f3f4f6';
                }
            ?>
                <rect
                    x="<?php echo $x; ?>"
                    y="<?php echo $y; ?>"
                    width="12"
                    height="48"
                    rx="6"
                    ry="6"
                    fill="<?php echo esc_attr($color); ?>"
                    style="<?php echo $style; ?>"
                    transform="rotate(<?php echo $rotation; ?> <?php echo $x + 6; ?> <?php echo $y + 18; ?>)"
                />
            <?php endfor; ?>

            <!-- TEXTO CENTRAL (Glow ajustado también para consistencia) -->
            <text x="160" y="150"
                  text-anchor="middle"
                  font-size="42"
                  font-weight="700"
                  fill="#F9B137"
                  style="filter: drop-shadow(0 0 5px rgba(249,177,55, 0.5));">
                <?php echo esc_html($average); ?>%
            </text>

            <text x="160" y="170"
                  text-anchor="middle"
                  font-size="14"
                  fill="white">
                Avance total
            </text>

        </svg>

        <!-- LISTA DE CURSOS -->
        <div class="gptwp-course-list">
            <?php foreach ($course_data as $course): ?>
                <div class="gptwp-course-item">
                    <span class="titulo_members"><?php echo esc_html($course['title']); ?></span>
                    <strong><?php echo esc_html($course['percent']); ?>%</strong>
                </div>
            <?php endforeach; ?>
        </div>

    </div>

    <style>
		.titulo_members{
			color: white;
		}
        .gptwp-premium-gauge {
            max-width: 460px;
            margin: 0 auto;
        }

        .gptwp-gauge-svg {
            width: 100%;
            height: auto;
            display: block;
            /* Importante: permite que el glow se salga de la caja si es necesario */
            overflow: visible; 
        }

        .gptwp-course-list {
            margin-top: 24px;
            display: flex;
            flex-direction: column;
            gap: 10px;
            font-size: 14px;
        }

        .gptwp-course-item {
            display: flex;
            justify-content: space-between;
            border-bottom: 1px solid #e5e7eb;
            padding-bottom: 6px;
        }

        .gptwp-course-item strong {
            color: #F9B137;
        }
    </style>

    <?php
    return ob_get_clean();
}
add_shortcode('gptwp_premium_course_progress', 'gptwp_premium_course_progress');

function gptwp_show_total_time() {
    $user_id = get_current_user_id();
    $total_time = (int) get_user_meta($user_id, '_gptwp_total_time', true);

    // Si no existe, lo tratamos como 0
    if (!$total_time) {
        $total_time = 0;
    }

    $hours = floor($total_time / 3600);
    $minutes = floor(($total_time % 3600) / 60);
    $seconds = $total_time % 60;

    return "Has estudiado {$hours} hrs {$minutes} min {$seconds} s en total.";
}
add_shortcode('gptwp_total_time', 'gptwp_show_total_time');


/**
 * ===========================================
 *  SISTEMA DE TRACKING DE TIEMPO DE ESTUDIO
 * ===========================================
 */

// 🔹 1. Crear la tabla al activar el tema
function wp_tiempo_estudio_crear_tabla() {
    global $wpdb;
    $tabla = $wpdb->prefix . 'tiempo_estudio';
    $charset = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $tabla (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        total_segundos BIGINT(20) UNSIGNED NOT NULL DEFAULT 0,
        updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY user_id (user_id)
    ) $charset;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_switch_theme', 'wp_tiempo_estudio_crear_tabla');

// -----------------------------
// Guardar tiempo de estudio (functions.php)
// -----------------------------
add_action('wp_ajax_game_guardar_tiempo_estudio', 'wp_guardar_tiempo_estudio');
add_action('wp_ajax_nopriv_game_guardar_tiempo_estudio', 'wp_guardar_tiempo_estudio');

function wp_guardar_tiempo_estudio() {
    global $wpdb;

    // Log inicial (debug)
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[tiempo_estudio] AJAX recibido. REMOTE_USER: ' . (is_user_logged_in() ? 'SI' : 'NO'));
        error_log('[tiempo_estudio] $_POST: ' . print_r($_POST, true));
    }

    // 1) Requerir usuario logueado
    if (!is_user_logged_in()) {
        wp_send_json_error(['error' => 'Usuario no autenticado']);
    }

    $user_id = get_current_user_id();
    $tiempo = isset($_POST['tiempo']) ? intval($_POST['tiempo']) : 0;

    if ($tiempo <= 0) {
        wp_send_json_error(['error' => 'Tiempo inválido']);
    }

    // 2) Nombre de la tabla (exacto)
    $tabla = 'wp_tiempo_estudio';

    // 3) Intentar actualizar (sumar) — si falla, intentamos insertar
    $existe = $wpdb->get_var( $wpdb->prepare("SELECT total_segundos FROM {$tabla} WHERE user_id = %d", $user_id) );

    if ($existe !== null) {
        $ok = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$tabla} SET total_segundos = total_segundos + %d, updated_at = NOW() WHERE user_id = %d",
                $tiempo, $user_id
            )
        );
        if ($ok === false) {
            if (defined('WP_DEBUG') && WP_DEBUG) error_log("[tiempo_estudio] ERROR update user $user_id");
            wp_send_json_error(['error' => 'Error al actualizar registro']);
        }
    } else {
        $ok = $wpdb->insert(
            $tabla,
            [
                'user_id' => $user_id,
                'total_segundos' => $tiempo,
                'updated_at' => current_time('mysql')
            ],
            ['%d','%d','%s']
        );
        if ($ok === false) {
            if (defined('WP_DEBUG') && WP_DEBUG) error_log("[tiempo_estudio] ERROR insert user $user_id");
            wp_send_json_error(['error' => 'Error al insertar registro']);
        }
    }

    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log("[tiempo_estudio] OK guardado user {$user_id} +{$tiempo}s");
    }

    wp_send_json_success(['mensaje' => 'Tiempo guardado', 'tiempo' => $tiempo]);
}

// 🔹 3. Shortcode para mostrar tiempo total
function wp_tiempo_estudio_shortcode() {
    if (!is_user_logged_in()) {
        return "<p>Debes iniciar sesión para ver tu progreso.</p>";
    }

    global $wpdb;
    $user_id = get_current_user_id();
    $tabla = $wpdb->prefix . 'tiempo_estudio';
    $total = intval($wpdb->get_var($wpdb->prepare("SELECT total_segundos FROM $tabla WHERE user_id = %d", $user_id)));

    if ($total <= 0) {
        return "<p>Aún no tienes tiempo registrado.</p>";
    }

    $minutos = floor($total / 60);
    $segundos = $total % 60;

    return "<p>{$minutos} Min {$segundos} S</strong>.</p>";
}
add_shortcode('tiempo_estudio', 'wp_tiempo_estudio_shortcode');


// FUNCION PARA SABER EL TIEMPO DE ESTUDIO SEMANALMENTE

add_action( 'wp_footer', 'academy_enqueue_video_tracker' );
function academy_enqueue_video_tracker() {
    if ( ! is_user_logged_in() ) return;
    $post_types_to_track = array( 'sfwd-lessons', 'sfwd-topic', 'sfwd-courses' );
    if ( is_singular( $post_types_to_track ) ) {
        $ajax_url = admin_url('admin-ajax.php');
        $nonce = wp_create_nonce("academy_video_timer_nonce");
        ?>
        <script type="text/javascript">
        (function() {
            let isPlaying = false;
            let sessionSeconds = 0;
            let lastTickTime = 0;
            let safetyInterval;

            window.addEventListener('message', function(e) {
                if (!e.data) return;
                let msg = e.data;
                let dataObj = null;
                try {
                    if (typeof msg === 'string' && msg.trim().startsWith('{')) {
                        dataObj = JSON.parse(msg);
                    } else if (typeof msg === 'object') {
                        dataObj = msg;
                    }
                } catch (err) { }

                let eventType = '';
                if (dataObj) {
                    if (dataObj.type && typeof dataObj.type === 'string' && dataObj.type.indexOf('@vdo') !== -1 && dataObj.payload) {
                        if (dataObj.payload.elementEventName) {
                            let name = dataObj.payload.elementEventName;
                            if (name === 'timeupdate' || name === 'playing') eventType = 'playing';
                            else if (name === 'pause' || name === 'ended') eventType = 'pause';
                        }
                        if (dataObj.payload.videoState) {
                            if (dataObj.payload.videoState.paused === false) eventType = 'playing';
                            else if (dataObj.payload.videoState.paused === true) eventType = 'pause';
                        }
                    }
                    let fallbackName = dataObj.event || dataObj.type || '';
                    if (fallbackName === 'timeupdate' || fallbackName === 'playing') eventType = 'playing';
                    else if (fallbackName === 'pause' || fallbackName === 'ended') eventType = 'pause';
                }

                if (eventType === 'playing') {
                    if (!isPlaying) startCounting(); 
                } else if (eventType === 'pause') {
                    if (isPlaying) stopAndSend(); 
                }
            });

            function startCounting() {
                isPlaying = true;
                lastTickTime = Date.now();
                safetyInterval = setInterval(function() {
                    if(isPlaying) tick(); 
                    if(sessionSeconds >= 60) { 
                        sendDataToServer(sessionSeconds); 
                        sessionSeconds = 0; 
                    }
                }, 1000); 
            }

            function stopAndSend() {
                isPlaying = false;
                clearInterval(safetyInterval);
                tick(); 
                if(sessionSeconds > 2) sendDataToServer(sessionSeconds);
                sessionSeconds = 0;
            }

            function tick() {
                let now = Date.now();
                let delta = (now - lastTickTime) / 1000; 
                if (delta > 0 && delta < 5) sessionSeconds += delta;
                lastTickTime = now;
            }

            function sendDataToServer(seconds) {
                if (seconds <= 0) return;
                const formData = new FormData();
                formData.append('action', 'academy_log_video_time');
                formData.append('security', '<?php echo $nonce; ?>');
                formData.append('seconds', seconds); 
                if (navigator.sendBeacon) navigator.sendBeacon('<?php echo $ajax_url; ?>', formData);
                else fetch('<?php echo $ajax_url; ?>', { method: 'POST', body: formData, keepalive: true }).catch(err => console.error(err));
            }

            function handleExit() {
                if (isPlaying) {
                    tick();
                    if (sessionSeconds > 0) sendDataToServer(sessionSeconds);
                }
            }
            window.addEventListener("beforeunload", handleExit);
            document.addEventListener("visibilitychange", function() {
                if (document.visibilityState === 'hidden') handleExit();
            });
        })();
        </script>
        <?php
    }
}

// ==============================================================================
// 2. PROCESAMIENTO EN SERVIDOR (AJAX)
// ==============================================================================

add_action( 'wp_ajax_academy_log_video_time', 'academy_ajax_log_video_time' );
function academy_ajax_log_video_time() {
    check_ajax_referer( 'academy_video_timer_nonce', 'security' );
    if ( ! is_user_logged_in() ) wp_die();

    $user_id = get_current_user_id();
    $seconds_to_add = isset($_POST['seconds']) ? floatval($_POST['seconds']) : 0;
    $seconds_int = round($seconds_to_add);
    if ($seconds_int <= 0) wp_die();

    // Guardar DÍA
    $today_key = 'academy_day_' . date('Y-m-d'); 
    $current_day = (int) get_user_meta( $user_id, $today_key, true );
    update_user_meta( $user_id, $today_key, $current_day + $seconds_int );

    // Guardar MES
    $month_key = 'academy_month_' . date('Y-m');
    $current_month = (int) get_user_meta( $user_id, $month_key, true );
    update_user_meta( $user_id, $month_key, $current_month + $seconds_int );
    
    // Guardar TOTAL
    $global_total = (int) get_user_meta($user_id, 'academy_total_lifetime', true);
    update_user_meta($user_id, 'academy_total_lifetime', $global_total + $seconds_int);

    wp_die();
}

// ==============================================================================
// 3. SHORTCODE WIDGET DUAL (SEMANA / AÑO)
// ==============================================================================
add_shortcode('study_progress_widget', 'academy_render_progress_widget');
function academy_render_progress_widget() {
    if ( ! is_user_logged_in() ) return '';
    $user_id = get_current_user_id();
    $widget_id = uniqid('apw_'); 

    // --- LÓGICA 1: SEMANAL ---
    $start_of_week = new DateTime();
    $start_of_week->setISODate((int)date('o'), (int)date('W')); 
    $week_data = [];
    $week_max = 1;
    $week_total = 0;
    $dias_esp = ['LUN', 'MAR', 'MIE', 'JUE', 'VIE', 'SAB', 'DOM'];

    $temp_date = clone $start_of_week;
    for ($i = 0; $i < 7; $i++) {
        $date_key = $temp_date->format('Y-m-d'); 
        $sec = (int) get_user_meta($user_id, 'academy_day_' . $date_key, true);
        $week_total += $sec;
        if ($sec > $week_max) $week_max = $sec;

        $week_data[] = [
            'label' => $dias_esp[$i],
            'seconds' => $sec,
            'is_today' => ($date_key === date('Y-m-d')),
            'tooltip' => academy_format_tooltip($sec)
        ];
        $temp_date->modify('+1 day');
    }

    // --- LÓGICA 2: ANUAL ---
    $current_year = date('Y');
    $year_data = [];
    $year_max = 1;
    $year_total = 0;
    $meses_esp = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];

    for ($m = 1; $m <= 12; $m++) {
        $month_key_str = sprintf('%s-%02d', $current_year, $m);
        $meta_key = 'academy_month_' . $month_key_str;
        $sec = (int) get_user_meta($user_id, $meta_key, true);
        
        if ($sec == 0) {
            $start_m = new DateTime("$current_year-$m-01");
            $end_m   = new DateTime("$current_year-$m-01"); 
            $end_m->modify('last day of this month');
            while ($start_m <= $end_m) {
                $sec += (int) get_user_meta($user_id, 'academy_day_' . $start_m->format('Y-m-d'), true);
                $start_m->modify('+1 day');
            }
        }
        $year_total += $sec;
        if ($sec > $year_max) $year_max = $sec;

        $year_data[] = [
            'label' => $meses_esp[$m-1],
            'seconds' => $sec,
            'is_today' => ($m == (int)date('n')), 
            'tooltip' => academy_format_tooltip($sec)
        ];
    }

    // Eje Y
    $w_max_h = round($week_max / 3600, 1) . 'h';
    $w_mid_h = round(($week_max / 2) / 3600, 1) . 'h';
    $y_max_h = round($year_max / 3600, 1) . 'h';
    $y_mid_h = round(($year_max / 2) / 3600, 1) . 'h';

    ob_start();
    ?>
    <style>
        .academy-progress-widget { 
            position: relative; width: 100%; display: flex; flex-direction: column;
            font-family: 'Helvetica Neue', Arial, sans-serif; color: #fff; background: transparent;
        }
        .apw-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; z-index:1;}
        .apw-label { font-size: 10px; text-transform: uppercase; color: #888; letter-spacing: 1px; display: block; margin-bottom: 4px; }
        .apw-total { font-size: 35px; font-weight: 700; margin: 0; line-height: 1; color: #fff; white-space: nowrap; }
        
        /* BOTONES */
        .apw-toggle-container { background: #1a1a1a; border-radius: 50px; padding: 6px; display: inline-flex; border: 1px solid #333; }
        .apw-toggle-btn { background: transparent; border: none; color: #777; padding: 6px 14px; font-size: 11px; font-weight: 700; border-radius: 50px; cursor: pointer; transition: 0.3s; outline: none; }
		.apw-toggle-btn:hover { background: #F9B137; border: none; color: #000; padding: 6px 14px; font-size: 11px; font-weight: 700; border-radius: 50px; cursor: pointer; transition: 0.3s; outline: none; }
        .apw-toggle-btn.active { background: #F9B137; color: #000; box-shadow: 0 2px 8px rgba(249,177,55,0.3); }

        /* BODY - ALTURA FIJA PARA EVITAR COLAPSO */
        .apw-body-wrapper { 
            display: flex; width: 100%; 
            height: 250px; /* Altura segura */
            position: relative;
        }

        /* EJE Y - SIN LÍNEA DIVISORIA */
        .apw-y-axis-col {
            width: 35px; height: 100%; flex-shrink: 0; position: relative; margin-right: 10px;
            border-right: none !important; /* QUITAR LÍNEA EXPLÍCITAMENTE */
        }
        .apw-y-labels { width: 100%; height: 100%; display: none; flex-direction: column; justify-content: space-between; padding-bottom: 20px; }
        .apw-y-labels.active-axis { display: flex; }
        .apw-y-label { font-size: 9px; color: #666; text-align: right; padding-right: 5px; transform: translateY(50%); }
        .apw-y-label:first-child { transform: translateY(0); }
        .apw-y-label:last-child { transform: translateY(0); }

        /* CHART AREA */
        .apw-charts-area { flex: 1; position: relative; height: 100%; }
        
        /* QUITAR LÍNEAS DE GRID */
        .apw-grid-lines { display: none !important; }

        /* CONTAINER GRÁFICAS */
        .apw-chart-container { 
            width: 100%; height: 100%; display: none; justify-content: space-between; align-items: flex-end; gap: 4px; padding-bottom: 5px; 
            z-index: 1; position: relative;
        }
        .apw-chart-container.active-chart { display: flex; }

        /* BARRAS DEGRADADAS BONITAS */
        .apw-bar-group { flex: 1; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; position: relative; cursor: pointer; min-width: 0; transition: z-index 0s; }
        .apw-bar-group:hover { z-index: 100; } /* Tooltip al frente */

        .apw-bar-wrapper { 
            width: 100%; height: 100%; display: flex; align-items: flex-end; position: relative;
            background: transparent; border-radius: 4px;
        }
        
        .apw-bar { 
            width: 100%; 
            border-radius: 4px 4px 0 0; 
            transition: height 0.6s ease;
            min-height: 4px; 
            /* DEGRADADO DORADO A TRANSPARENTE */
            background: linear-gradient(to top, rgba(249, 177, 55, 0.1) 0%, #F9B137 100%);
            /* GLOW SUAVE */
            box-shadow: 0 0 15px rgba(249, 177, 55, 0.3);
            position: relative;
        }
        
        /* BARRA HOY (BLANCA) */
        .apw-bar-group.is-today .apw-bar { 
            background: linear-gradient(to top, rgba(255, 255, 255, 0.1) 0%, #ffffff 100%);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
        }
        
        .apw-day-label { font-size: 9px; color: #666; margin-top: 8px; text-align: center; font-weight: 600; width: 100%; overflow: hidden; white-space: nowrap; }
        
        .apw-tooltip { 
            position: absolute; top: -35px; left: 50%; transform: translateX(-50%) translateY(5px);
            background: #F9B137; color: #000; padding: 4px 8px; border-radius: 4px; font-size: 10px; font-weight: 800;
            opacity: 0; transition: 0.2s; pointer-events: none; white-space: nowrap; z-index: 200;
            box-shadow: 0 5px 15px rgba(0,0,0,0.5);
        }
        .apw-tooltip:after { content:''; position: absolute; bottom: -4px; left: 50%; transform: translateX(-50%); border-width: 4px 4px 0; border-style: solid; border-color: #F9B137 transparent transparent transparent; }
        .apw-bar-group:hover .apw-tooltip { opacity: 1; transform: translateX(-50%) translateY(0); }
        .apw-bar-group:hover .apw-bar { filter: brightness(1.2); }

        @media (max-width: 480px) {
            .apw-y-axis-col { width: 25px; margin-right: 5px; }
            .apw-day-label { font-size: 8px; transform: scale(0.9); }
        }
    </style>

    <div id="<?php echo $widget_id; ?>" class="academy-progress-widget">
        <!-- HEADER -->
        <div class="apw-header">
            <div class="apw-info">
                <span class="apw-label">Tu Rendimiento</span>
                <h3 class="apw-total" id="total_<?php echo $widget_id; ?>">
                    <?php echo academy_format_time_short($week_total); ?>
                </h3>
            </div>
            <div class="apw-toggle-container">
                <button class="apw-toggle-btn active" onclick="apwSwitch('<?php echo $widget_id; ?>', 'week', '<?php echo academy_format_time_short($week_total); ?>')">Semana</button>
                <button class="apw-toggle-btn" onclick="apwSwitch('<?php echo $widget_id; ?>', 'year', '<?php echo academy_format_time_short($year_total); ?>')">Año</button>
            </div>
        </div>

        <!-- BODY -->
        <div class="apw-body-wrapper">
            <!-- EJE Y -->
            <div class="apw-y-axis-col">
                <div id="axis_week_<?php echo $widget_id; ?>" class="apw-y-labels active-axis">
                    <span class="apw-y-label"><?php echo $w_max_h; ?></span>
                    <span class="apw-y-label"><?php echo $w_mid_h; ?></span>
                    <span class="apw-y-label">0h</span>
                </div>
                <div id="axis_year_<?php echo $widget_id; ?>" class="apw-y-labels">
                    <span class="apw-y-label"><?php echo $y_max_h; ?></span>
                    <span class="apw-y-label"><?php echo $y_mid_h; ?></span>
                    <span class="apw-y-label">0h</span>
                </div>
            </div>

            <!-- GRÁFICAS -->
            <div class="apw-charts-area">
                <div class="apw-grid-lines"></div> <!-- VACÍO/OCULTO -->

                <!-- SEMANA -->
                <div id="chart_week_<?php echo $widget_id; ?>" class="apw-chart-container active-chart">
                    <?php foreach ($week_data as $day): 
                        $pct = ($week_max > 0) ? ($day['seconds'] / $week_max) * 100 : 0;
                        if($day['seconds'] > 0 && $pct < 5) $pct = 5; 
                    ?>
                        <div class="apw-bar-group <?php echo $day['is_today'] ? 'is-today' : ''; ?>">
                            <div class="apw-tooltip"><?php echo $day['tooltip']; ?></div>
                            <div class="apw-bar-wrapper">
                                <div class="apw-bar" style="height: <?php echo $pct; ?>%;"></div>
                            </div>
                            <span class="apw-day-label"><?php echo $day['label']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- AÑO -->
                <div id="chart_year_<?php echo $widget_id; ?>" class="apw-chart-container">
                    <?php foreach ($year_data as $month): 
                        $pct = ($year_max > 0) ? ($month['seconds'] / $year_max) * 100 : 0;
                        if($month['seconds'] > 0 && $pct < 5) $pct = 5;
                    ?>
                        <div class="apw-bar-group <?php echo $month['is_today'] ? 'is-today' : ''; ?>">
                            <div class="apw-tooltip"><?php echo $month['tooltip']; ?></div>
                            <div class="apw-bar-wrapper">
                                <div class="apw-bar" style="height: <?php echo $pct; ?>%;"></div>
                            </div>
                            <span class="apw-day-label"><?php echo $month['label']; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <script>
    if (typeof apwSwitch === 'undefined') {
        window.apwSwitch = function(uid, type, totalText) {
            const root = document.getElementById(uid);
            root.querySelectorAll('.apw-toggle-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            root.querySelector('.apw-total').innerText = totalText;
            
            // Toggle DISPLAY para evitar colapsos
            root.querySelectorAll('.apw-chart-container').forEach(chart => chart.classList.remove('active-chart'));
            document.getElementById('chart_' + type + '_' + uid).classList.add('active-chart');

            root.querySelectorAll('.apw-y-labels').forEach(ax => ax.classList.remove('active-axis'));
            document.getElementById('axis_' + type + '_' + uid).classList.add('active-axis');
        };
    }
    </script>
    <?php
    return ob_get_clean();
}

// Helpers
function academy_format_tooltip($seconds) {
    if ($seconds == 0) return "0s";
    $h = floor($seconds / 3600);
    $m = floor(($seconds / 60) % 60);
    $s = $seconds % 60;
    return sprintf('%dh %02dm %02ds', $h, $m, $s);
}
function academy_format_time_short($seconds) {
    if ($seconds == 0) return "0s";
    $h = floor($seconds / 3600);
    $m = floor(($seconds / 60) % 60);
    $s = $seconds % 60;
    return sprintf('%dh %02dm %02ds', $h, $m, $s);
}
