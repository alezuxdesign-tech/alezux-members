<?php
// ==============================================================================
// MÓDULO: EMAIL MARKETING Y FILTROS
// ==============================================================================

// 1. FILTRO: REMITENTE DE CORREO
add_filter('wp_mail_from_name', function($original_name) {
    return 'CDI BUSINESS SCHOOL';
});

// 2. PLANTILLA DE EMAIL CORPORATIVA
function gptwp_get_email_template($data) {
    $brand_color = '#F9B137'; // Oro
    $bg_dark = '#0a0a0a';      
    $bg_card = '#141414';      
    $logo_url = 'https://academia.cdibusinessschool.com/wp-content/uploads/2025/12/LOGO_WHITE.webp';
    
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            body { margin: 0; padding: 0; background-color: <?php echo $bg_dark; ?>; font-family: 'Helvetica', Arial, sans-serif; }
            .container { max-width: 600px; margin: 40px auto; background: <?php echo $bg_card; ?>; border-radius: 20px; overflow: hidden; border: 1px solid #333; }
            .header { background-color: #000000; padding: 40px 20px; text-align: center; border-bottom: 3px solid <?php echo $brand_color; ?>; }
            .body { padding: 40px; color: #ffffff; line-height: 1.6; }
            .credentials { background: #000; border-left: 4px solid <?php echo $brand_color; ?>; padding: 20px; border-radius: 12px; margin: 30px 0; }
            .button { display: inline-block; background: <?php echo $brand_color; ?>; color: #000000 !important; padding: 15px 30px; text-decoration: none; border-radius: 50px; font-weight: bold; text-transform: uppercase; margin-top: 20px; }
            .footer { background: #000; padding: 20px; text-align: center; font-size: 12px; color: #666; border-top: 1px solid #222; }
            h2 { color: <?php echo $brand_color; ?>; margin-top: 0; }
            p { margin-bottom: 15px; color: #ccc; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <img src="<?php echo esc_url($logo_url); ?>" alt="CDI Logo" style="max-width: 250px; height: auto;">
            </div>
            <div class="body">
                <h2>¡Hola, <?php echo esc_html($data['name']); ?>!</h2>
                
                <?php if ($data['is_new_user']): ?>
                    <p>Bienvenido a la élite empresarial. Tu cuenta ha sido activada exitosamente.</p>
                    <div class="credentials">
                        <p style="margin:0 0 10px; color:#fff; font-weight:bold;">Tus credenciales:</p>
                        <p style="margin:5px 0;">Usuario: <span style="color:<?php echo $brand_color; ?>;"><?php echo esc_html($data['username']); ?></span></p>
                        <p style="margin:5px 0;">Contraseña: <span style="color:<?php echo $brand_color; ?>;"><?php echo esc_html($data['password']); ?></span></p>
                    </div>
                <?php else: ?>
                    <p>Hemos actualizado tu perfil con acceso a nuevos programas de formación.</p>
                <?php endif; ?>

                <div style="text-align: center;">
                    <a href="<?php echo esc_url($data['login_url']); ?>" class="button">Acceder a la Academia</a>
                </div>
            </div>
            <div class="footer">
                &copy; <?php echo date('Y'); ?> CDI BUSINESS SCHOOL. Liderazgo y Negocios.
            </div>
        </div>
    </body>
    </html>
    <?php
    return ob_get_clean();
}

// 3. INICIALIZACIÓN (TABLA LOGS + CONFIGURACIÓN)
add_action('init', function() {
    global $wpdb;
    $tabla = $wpdb->prefix . 'gptwp_email_logs';
    
    // Crear tabla de historial si no existe
    if($wpdb->get_var("SHOW TABLES LIKE '$tabla'") != $tabla) {
        $charset_collate = $wpdb->get_charset_collate();
        $sql = "CREATE TABLE $tabla (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            email_type varchar(50) NOT NULL,
            subject varchar(255) NOT NULL,
            sent_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
});

// 4. SHORTCODE PRINCIPAL DE GESTIÓN (LIVE PREVIEW)
// Uso: [admin_gestor_correos]
add_shortcode('admin_gestor_correos', function() {
    if (!current_user_can('manage_options') && !current_user_can('shop_manager')) return '';
    
    wp_enqueue_script('jquery');
    
    $emails_config = [
        'welcome' => ['label' => 'Bienvenida', 'vars' => '{nombre}, {usuario}, {password}, {login_url}', 'desc' => 'Al registrar nuevo alumno.'],
        'payment_due_soon' => ['label' => 'Cobro Preventivo', 'vars' => '{nombre}, {monto}, {fecha_vencimiento}, {link_pago}', 'desc' => '2 días antes de vencer.'],
        'payment_overdue' => ['label' => 'Pago Vencido', 'vars' => '{nombre}, {monto}, {dias_atraso}, {link_pago}', 'desc' => 'Al detectar mora.'],
        'payment_received' => ['label' => 'Pago Exitoso', 'vars' => '{nombre}, {monto}, {concepto}, {id_transaccion}', 'desc' => 'Tras pago confirmado.'],
        'inactivity_rescue' => ['label' => 'Rescate', 'vars' => '{nombre}, {dias_ausente}, {ultima_clase}, {link_retomar}', 'desc' => 'Tras inactividad.'],
        'high_performance' => ['label' => 'Felicitación', 'vars' => '{nombre}, {horas_semana}, {clases_completadas}', 'desc' => 'Al superar metas.']
    ];

    ob_start();
    ?>
    <div class="gptwp-email-manager-wrapper">
        
        <div class="gptwp-em-header">
            <h2 class="gptwp-em-title"><span class="dashicons dashicons-email-alt"></span> Gestor de Comunicaciones</h2>
            <div class="gptwp-em-tabs">
                <button class="em-tab-btn active" onclick="switchEmTab('history')">📜 Historial</button>
                <?php foreach($emails_config as $key => $inf): ?>
                    <button class="em-tab-btn" onclick="switchEmTab('<?php echo $key; ?>')"><?php echo $inf['label']; ?></button>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- TAB: HISTORIAL -->
        <div id="tab_content_history" class="gptwp-em-content active">
            <div id="email_history_table">
                <p style="text-align:center; color:#666;">Cargando historial...</p>
            </div>
        </div>

        <!-- TABS: EDITORES CON LIVE PREVIEW -->
        <?php foreach($emails_config as $key => $info): 
            $subject = get_option("gptwp_email_{$key}_subject", "");
            $html_code = get_option("gptwp_email_{$key}_body", ""); // Ahora guarda HTML completo
            $active = get_option("gptwp_email_{$key}_active", 0);
        ?>
        <div id="tab_content_<?php echo $key; ?>" class="gptwp-em-content" style="display:none;">
            <form class="gptwp-email-form" data-key="<?php echo $key; ?>">
                
                <div class="em-top-bar">
                    <div class="em-info">
                        <span class="em-label"><?php echo $info['desc']; ?></span>
                        <code class="em-vars">Variables: <?php echo $info['vars']; ?></code>
                    </div>
                    <div class="em-actions">
                        <label class="switch">
                            <input type="checkbox" name="active" value="1" <?php checked(1, $active); ?>>
                            <span class="slider round"></span>
                        </label>
                        <span class="switch-label">Activar</span>
                        <button type="submit" class="gptwp-btn-save">GUARDAR CAMBIOS</button>
                    </div>
                </div>

                <div class="em-subject-row">
                    <label>Asunto:</label>
                    <input type="text" name="subject" value="<?php echo esc_attr($subject); ?>" placeholder="Asunto del correo..." class="em-input">
                </div>

                <!-- SPLIT VIEW EDITOR -->
                <div class="em-split-view">
                    <div class="em-code-pane">
                        <div class="pane-header">CÓDIGO HTML (Pegar plantilla aquí)</div>
                        <textarea name="body" class="em-code-area" id="code_<?php echo $key; ?>" oninput="updatePreview('<?php echo $key; ?>')" placeholder="<html>...</html>"><?php echo esc_textarea($html_code); ?></textarea>
                    </div>
                    <div class="em-preview-pane">
                        <div class="pane-header">VISTA PREVIA REAL</div>
                        <iframe id="preview_<?php echo $key; ?>" class="em-preview-frame"></iframe>
                    </div>
                </div>

            </form>
        </div>
        <?php endforeach; ?>
        
        <div id="gptwp_toast" class="gptwp-toast"></div>
    </div>

    <style>
        .gptwp-email-manager-wrapper { font-family: 'Manrope', sans-serif; color: #fff; width: 100%; box-sizing: border-box; }
        
        /* Header */
        .gptwp-em-header { border-bottom: 1px solid #333; margin-bottom: 20px; padding-bottom: 15px; }
        .gptwp-em-title { font-size: 24px; color: #fff; margin: 0 0 20px 0; display: flex; align-items: center; gap: 10px; }
        
        /* Tabs */
        .gptwp-em-tabs { display: flex; gap: 5px; flex-wrap: wrap; }
        .em-tab-btn { background: #141414; border: 1px solid #333; color: #888; padding: 10px 20px; border-radius: 6px; cursor: pointer; transition: 0.2s; font-size: 13px; font-weight: 700; text-transform: uppercase; }
        .em-tab-btn:hover { background: #222; color: #fff; }
        .em-tab-btn.active { background: #F9B137; color: #000; border-color: #F9B137; }

        /* Content Area */
        .gptwp-em-content { animation: fadeIn 0.3s; }

        /* Top Bar */
        .em-top-bar { display: flex; justify-content: space-between; align-items: center; background: #141414; padding: 15px; border-radius: 8px 8px 0 0; border: 1px solid #333; border-bottom: none; flex-wrap: wrap; gap: 15px; }
        .em-info { flex: 1; }
        .em-label { display: block; font-size: 13px; color: #ccc; margin-bottom: 5px; }
        .em-vars { font-size: 11px; color: #4dff88; font-family: monospace; background: #000; padding: 4px 8px; border-radius: 4px; }
        
        .em-actions { display: flex; align-items: center; gap: 15px; }
        
        /* Subject Row */
        .em-subject-row { background: #1a1a1a; padding: 15px; border: 1px solid #333; display: flex; align-items: center; gap: 15px; }
        .em-subject-row label { font-size: 12px; font-weight: 800; color: #888; text-transform: uppercase; }
        .em-input { flex: 1; background: #000; border: 1px solid #444; color: #fff; padding: 10px; border-radius: 4px; font-size: 14px; }
        .em-input:focus { border-color: #F9B137; outline: none; }

        /* Split View (Editor + Preview) */
        .em-split-view { display: grid; grid-template-columns: 1fr 1fr; height: 600px; border: 1px solid #333; border-top: none; border-radius: 0 0 8px 8px; overflow: hidden; }
        
        .em-code-pane, .em-preview-pane { display: flex; flex-direction: column; height: 100%; }
        .em-code-pane { border-right: 1px solid #333; background: #111; }
        .em-preview-pane { background: #fff; } /* Fondo blanco para ver el email real */
        
        .pane-header { background: #000; color: #888; padding: 8px 15px; font-size: 10px; text-transform: uppercase; font-weight: 800; border-bottom: 1px solid #333; letter-spacing: 1px; }
        
        .em-code-area { flex: 1; background: #111; color: #ddd; border: none; padding: 15px; font-family: 'Consolas', monospace; font-size: 12px; line-height: 1.5; resize: none; width: 100%; box-sizing: border-box; outline: none; }
        .em-preview-frame { flex: 1; width: 100%; border: none; background: #fff; }

        /* Botón Guardar */
        .gptwp-btn-save { background: #F9B137; color: #000; border: none; padding: 10px 25px; border-radius: 50px; font-weight: 800; cursor: pointer; text-transform: uppercase; font-size: 12px; transition: 0.3s; }
        .gptwp-btn-save:hover { background: #fff; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(249,177,55,0.2); }

        /* Switch */
        .switch { position: relative; display: inline-block; width: 40px; height: 20px; }
        .switch input { opacity: 0; width: 0; height: 0; }
        .slider { position: absolute; cursor: pointer; top: 0; left: 0; right: 0; bottom: 0; background-color: #333; transition: .4s; border-radius: 34px; }
        .slider:before { position: absolute; content: ""; height: 14px; width: 14px; left: 3px; bottom: 3px; background-color: white; transition: .4s; border-radius: 50%; }
        input:checked + .slider { background-color: #F9B137; }
        input:checked + .slider:before { transform: translateX(20px); }
        .switch-label { font-size: 12px; color: #fff; font-weight: 600; }

        /* Historial Table */
        .log-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .log-table th { text-align: left; color: #F9B137; padding: 15px; border-bottom: 1px solid #333; text-transform: uppercase; font-size: 11px; }
        .log-table td { padding: 15px; border-bottom: 1px solid #222; color: #ccc; }
        .log-status { color: #4dff88; background: rgba(77, 255, 136, 0.1); padding: 3px 8px; border-radius: 4px; font-size: 10px; text-transform: uppercase; border: 1px solid rgba(77, 255, 136, 0.2); }

        /* Toast */
        .gptwp-toast { visibility: hidden; position: fixed; bottom: 30px; left: 50%; transform: translateX(-50%); background: #333; color: #fff; padding: 12px 24px; border-radius: 50px; z-index: 100000; transition: 0.3s; opacity: 0; border: 1px solid #444; }
        .gptwp-toast.show { visibility: visible; bottom: 50px; opacity: 1; }

        @media(max-width: 900px) { .em-split-view { grid-template-columns: 1fr; height: 800px; } .em-code-pane { border-right: none; border-bottom: 1px solid #333; height: 400px; } }
    </style>

    <script>
    jQuery(document).ready(function($) {
        const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';

        // 1. Cambio de Pestañas
        window.switchEmTab = function(tabId) {
            $('.gptwp-em-content').hide();
            $('#tab_content_' + tabId).show();
            $('.em-tab-btn').removeClass('active');
            
            $('.em-tab-btn').each(function() {
                if($(this).text().includes(tabId) || $(this).attr('onclick').includes(tabId)) {
                    $(this).addClass('active');
                }
            });

            if(tabId === 'history') loadHistory();
            else updatePreview(tabId); // Renderizar preview al abrir tab
        };

        // 2. Live Preview Logic
        window.updatePreview = function(key) {
            let code = $('#code_' + key).val();
            let iframe = document.getElementById('preview_' + key);
            let doc = iframe.contentDocument || iframe.contentWindow.document;
            doc.open();
            doc.write(code);
            doc.close();
        };

        // 3. Cargar Historial
        function loadHistory() {
            $('#email_history_table').css('opacity', 0.5);
            $.post(ajaxUrl, { action: 'gptwp_get_email_history' }, function(res) {
                $('#email_history_table').html(res.data).css('opacity', 1);
            });
        }

        // 4. Guardar (AJAX)
        $('.gptwp-email-form').on('submit', function(e) {
            e.preventDefault();
            let form = $(this);
            let btn = form.find('.gptwp-btn-save');
            let key = form.data('key');
            
            let data = {
                action: 'gptwp_save_email_config',
                key: key,
                active: form.find('input[name="active"]').is(':checked') ? 1 : 0,
                subject: form.find('input[name="subject"]').val(),
                body: form.find('textarea[name="body"]').val() // Envía el HTML completo
            };

            btn.prop('disabled', true).text('Guardando...');

            $.post(ajaxUrl, data, function(res) {
                btn.prop('disabled', false).text('GUARDAR CONFIGURACIÓN');
                showToast(res.success ? 'Plantilla guardada' : 'Error al guardar');
            });
        });

        function showToast(msg) {
            $('#gptwp_toast').text(msg).addClass('show');
            setTimeout(() => $('#gptwp_toast').removeClass('show'), 3000);
        }

        loadHistory();
        
        // Inicializar previews ocultos (para que estén listos)
        <?php foreach($emails_config as $k => $i): ?>
            updatePreview('<?php echo $k; ?>');
        <?php endforeach; ?>
    });
    </script>
    <?php
    return ob_get_clean();
});

// 5. AJAX: GUARDAR CONFIGURACIÓN
add_action('wp_ajax_gptwp_save_email_config', function() {
    if (!current_user_can('manage_options') && !current_user_can('shop_manager')) wp_send_json_error();
    
    $key = sanitize_text_field($_POST['key']);
    $active = intval($_POST['active']);
    $subject = sanitize_text_field($_POST['subject']);
    // Importante: wp_kses_post a veces filtra CSS de email, usamos base64 o raw si eres admin
    // Para seguridad, usamos current_user_can. Si es admin, permitimos HTML crudo para plantillas.
    if (current_user_can('unfiltered_html')) {
        $body = $_POST['body']; 
    } else {
        $body = wp_kses_post($_POST['body']);
    }

    update_option("gptwp_email_{$key}_active", $active);
    update_option("gptwp_email_{$key}_subject", $subject);
    update_option("gptwp_email_{$key}_body", $body);

    wp_send_json_success();
});

// 6. AJAX: OBTENER HISTORIAL
add_action('wp_ajax_gptwp_get_email_history', function() {
    if (!current_user_can('manage_options') && !current_user_can('shop_manager')) wp_send_json_error();
    global $wpdb;
    $table = $wpdb->prefix . 'gptwp_email_logs';
    $logs = $wpdb->get_results("SELECT * FROM $table ORDER BY sent_at DESC LIMIT 20");

    if (empty($logs)) {
        wp_send_json_success('<div style="text-align:center; padding:30px; color:#666;">No hay correos enviados recientes.</div>');
    }

    ob_start();
    ?>
    <table class="log-table">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Usuario</th>
                <th>Tipo</th>
                <th>Asunto</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($logs as $log): 
                $user = get_userdata($log->user_id);
                $name = $user ? $user->display_name : 'ID: '.$log->user_id;
            ?>
            <tr>
                <td><?php echo date_i18n('d M Y H:i', strtotime($log->sent_at)); ?></td>
                <td><?php echo esc_html($name); ?></td>
                <td><span style="color:#aaa; background:#222; padding:2px 6px; border-radius:4px; font-size:10px;"><?php echo esc_html($log->email_type); ?></span></td>
                <td style="color:#fff;"><?php echo esc_html($log->subject); ?></td>
                <td><span class="log-status">Enviado</span></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php
    wp_send_json_success(ob_get_clean());
});

// 7. FUNCIÓN MAESTRA DE ENVÍO (Modificada para HTML Completo)
function gptwp_enviar_email_plantilla($user_id, $template_key, $extra_vars = []) {
    if (!get_option("gptwp_email_{$template_key}_active")) return false;

    $user = get_userdata($user_id);
    if (!$user) return false;

    $subject = get_option("gptwp_email_{$template_key}_subject");
    $body_html = get_option("gptwp_email_{$template_key}_body"); // HTML Completo

    if (empty($subject) || empty($body_html)) return false;

    $vars = [
        '{nombre}' => $user->first_name ?: $user->display_name,
        '{usuario}' => $user->user_login,
        '{email}' => $user->user_email,
        '{login_url}' => wp_login_url()
    ];
    $vars = array_merge($vars, $extra_vars);

    foreach ($vars as $k => $v) {
        $subject = str_replace($k, $v, $subject);
        $body_html = str_replace($k, $v, $body_html);
    }

    // Ya no usamos wrapper, enviamos el HTML directo que el usuario diseñó
    $headers = ['Content-Type: text/html; charset=UTF-8'];
    $sent = wp_mail($user->user_email, $subject, $body_html, $headers);

    if ($sent) {
        global $wpdb;
        $wpdb->insert(
            $wpdb->prefix . 'gptwp_email_logs',
            ['user_id' => $user_id, 'email_type' => $template_key, 'subject' => $subject, 'sent_at' => current_time('mysql')],
            ['%d', '%s', '%s', '%s']
        );
    }
    return $sent;
}
