<?php
/* -------------------------------------------------------------------------- */
/*                                SECCIÓN ADMINISTRATIVA & CRM                */
/* -------------------------------------------------------------------------- */

// Encolar scripts externos necesarios para el Dashboard (Chart.js, Flatpickr)
add_action('admin_enqueue_scripts', function() {
    if (current_user_can('manage_options') || current_user_can('shop_manager')) {
        // Flatpickr (Calendario)
        wp_enqueue_style('gptwp-flatpickr', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
        wp_enqueue_style('gptwp-flatpickr-dark', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css');
        wp_enqueue_script('gptwp-flatpickr-js', 'https://cdn.jsdelivr.net/npm/flatpickr', [], null, true);
        
        // Chart.js (Gráficas)
        wp_enqueue_script('gptwp-chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], null, true);
    }
});

// 1. SHORTCODE: GESTOR DE PERMISOS (CRM) V2
// Uso: [admin_gestor_permisos]

add_shortcode('admin_gestor_permisos', function() {
    if (!current_user_can('edit_users')) {
        return '<div style="background:red; color:white; padding:20px;">ACCESO DENEGADO</div>'; 
    }
    
    // Cargar iconos y scripts necesarios
    wp_enqueue_style('dashicons');

    ob_start();
    ?>
    <div class="gptwp-dashboard-wrapper">
        
        <!-- CABECERA PRINCIPAL -->
        <div class="gptwp-header-row">
            <div>
                <h2 class="gptwp-main-title">Centro de Mando Académico</h2>
                <p class="gptwp-subtitle">Gestión de accesos, datos personales y seguridad.</p>
            </div>
            <div class="gptwp-search-wrapper">
                <span class="dashicons dashicons-search gptwp-search-icon"></span>
                <input type="text" id="crm_search_input" placeholder="Buscar por nombre o email..." autocomplete="off">
            </div>
        </div>

        <!-- TABLA DE USUARIOS (CON WRAPPER RESPONSIVO) -->
        <div class="gptwp-card-table">
            <div class="gptwp-table-responsive">
                <table class="gptwp-crm-table">
                    <thead>
                        <tr>
                            <th width="60"></th>
                            <th>Estudiante</th>
                            <th>Email</th>
                            <th>Estado Cuenta</th>
                            <th style="text-align:right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="crm_results_body">
                        <tr>
                            <td colspan="5" style="text-align:center; padding:40px; color:#666;">
                                <span class="dashicons dashicons-update spin"></span> Cargando base de datos...
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <!-- PAGINACIÓN -->
            <div class="gptwp-pagination-row">
                <span id="gptwp_paging_info" class="gptwp-paging-info">Cargando...</span>
                <div class="gptwp-paging-controls">
                    <button id="btn_prev_page" class="btn-paging" disabled>&lsaquo; Anterior</button>
                    <button id="btn_next_page" class="btn-paging" disabled>Siguiente &rsaquo;</button>
                </div>
            </div>
        </div>

        <!-- NOTIFICACIÓN TOAST PERSONALIZADA -->
        <div id="gptwp_toast" class="gptwp-toast"></div>

    </div>

    <!-- MODAL DE GESTIÓN AVANZADA -->
    <div id="gptwp_permission_modal" class="gptwp-modal-overlay">
        <div class="gptwp-modal-content">
            
            <div class="gptwp-modal-header">
                <h3 style="margin:0; color:#fff;">Gestión de Usuario</h3>
                <button class="gptwp-modal-close">&times;</button>
            </div>

            <div id="gptwp_modal_body" class="gptwp-modal-body-scroll">
                <!-- Aquí se carga el contenido vía AJAX -->
            </div>

            <div class="gptwp-modal-footer">
                <div class="modal-footer-note">
                    * Los permisos de Lecciones/Temas requieren "Guardar". Datos y Bloqueos son inmediatos al pulsar su botón.
                </div>
                <button id="btn_save_permissions" class="gptwp-btn-save">
                    GUARDAR PERMISOS DE CONTENIDO
                </button>
            </div>
        </div>
    </div>

    <!-- Estilos movidos a assets/css/admin.css -->

    <script>
    jQuery(document).ready(function($) {
        const ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
        const modal = $('#gptwp_permission_modal');
        let currentEditingUser = 0;
        
        // --- PAGINACIÓN VARIABLES ---
        let currentPage = 1;
        let totalPages = 1;

        // --- SISTEMA DE NOTIFICACIONES (TOAST) ---
        function showNotification(message, type = 'success') {
            const toast = $('#gptwp_toast');
            let icon = type === 'success' ? 'dashicons-yes' : 'dashicons-warning';
            toast.html('<span class="dashicons '+icon+' gptwp-toast-icon"></span> ' + message);
            toast.removeClass('success error').addClass('show ' + type);
            setTimeout(function(){ toast.removeClass('show'); }, 3000);
        }

        // --- CARGA INICIAL ---
        loadUsers('', 1);

        // BUSCADOR
        let timeout = null;
        $('#crm_search_input').on('keyup', function() {
            clearTimeout(timeout);
            let val = $(this).val();
            // Reset page to 1 on search
            timeout = setTimeout(function() { loadUsers(val, 1); }, 500);
        });

        // --- PAGINACIÓN CLICKS ---
        $('#btn_prev_page').click(function() {
            if(currentPage > 1) loadUsers($('#crm_search_input').val(), currentPage - 1);
        });
        
        $('#btn_next_page').click(function() {
            if(currentPage < totalPages) loadUsers($('#crm_search_input').val(), currentPage + 1);
        });

        function loadUsers(term, page) {
            $('#crm_results_body').css('opacity', '0.5');
            
            $.post(ajaxUrl, { 
                action: 'gptwp_crm_get_users_v2', 
                term: term,
                page: page 
            }, function(res) {
                $('#crm_results_body').css('opacity', '1');
                
                if(res.success) {
                    // Render HTML
                    $('#crm_results_body').html(res.data.html);
                    
                    // Update Pagination UI
                    currentPage = res.data.pagination.current;
                    totalPages = res.data.pagination.pages;
                    
                    $('#gptwp_paging_info').text('Página ' + currentPage + ' de ' + totalPages + ' (' + res.data.pagination.total + ' estudiantes)');
                    
                    $('#btn_prev_page').prop('disabled', currentPage <= 1);
                    $('#btn_next_page').prop('disabled', currentPage >= totalPages);
                } else {
                    $('#crm_results_body').html('<tr><td colspan="5">Sin resultados</td></tr>');
                    $('#gptwp_paging_info').text('');
                    $('#btn_prev_page, #btn_next_page').prop('disabled', true);
                }
            });
        }

        // --- ABRIR MODAL ---
        $(document).on('click', '.btn-manage-user', function(e) {
            e.preventDefault();
            currentEditingUser = $(this).data('id');
            modal.addClass('is-visible');
            $('#gptwp_modal_body').html('<div style="text-align:center; padding:50px; color:#666;"><span class="dashicons dashicons-update spin"></span> Cargando perfil completo...</div>');

            $.post(ajaxUrl, { action: 'gptwp_crm_load_full_profile', user_id: currentEditingUser }, function(res) {
                if(res.success) {
                    $('#gptwp_modal_body').html(res.data);
                }
            });
        });

        $('.gptwp-modal-close').click(function() { modal.removeClass('is-visible'); });

        // --- TOGGLE EDITAR PERFIL ---
        $(document).on('click', '#btn_enable_edit_profile', function() {
            $('.profile-input-field').prop('disabled', false).first().focus();
            $(this).hide();
            $('#btn_update_profile').show();
        });

        // --- ACCIÓN: ACTUALIZAR DATOS PERSONALES ---
        $(document).on('click', '#btn_update_profile', function() {
            let name = $('#edit_display_name').val();
            let email = $('#edit_user_email').val();
            let btn = $(this);
            
            if(!name || !email) { showNotification('Nombre y Email son obligatorios', 'error'); return; }

            btn.text('...').prop('disabled', true);
            $.post(ajaxUrl, { action: 'gptwp_update_profile_data', user_id: currentEditingUser, name: name, email: email }, function(res) {
                btn.text('Actualizar').prop('disabled', false);
                if(res.success) { 
                    showNotification('Datos actualizados correctamente');
                    loadUsers($('#crm_search_input').val(), currentPage); // Reload current page
                    $('.profile-input-field').prop('disabled', true);
                    btn.hide();
                    $('#btn_enable_edit_profile').show();
                }
                else { showNotification('Error: ' + res.data, 'error'); }
            });
        });

        // --- ACCIÓN: CAMBIAR CONTRASEÑA ---
        $(document).on('click', '#btn_reset_pass', function() {
            let pass = $('#new_user_pass').val();
            if(pass.length < 4) { showNotification('La contraseña es muy corta', 'error'); return; }
            let btn = $(this);
            btn.text('Enviando...').prop('disabled', true);

            $.post(ajaxUrl, { action: 'gptwp_quick_action', type: 'password', user_id: currentEditingUser, value: pass }, function(res) {
                btn.text('Actualizar').prop('disabled', false);
                if(res.success) { 
                    showNotification('Contraseña actualizada y enviada al usuario');
                    $('#new_user_pass').val('');
                } else { 
                    // Mostrar el mensaje de error específico que viene del servidor (ej: fallo de wp_mail)
                    showNotification(res.data || 'Error al actualizar', 'error'); 
                }
            });
        });


        // --- ACCIÓN: GENERAR CONTRASEÑA Y ENVIAR ---
        $(document).on('click', '#btn_generate_pass', function() {
            // 1. Generar contraseña aleatoria
            const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
            let pass = "";
            for (let i = 0; i < 12; i++) {
                pass += chars.charAt(Math.floor(Math.random() * chars.length));
            }

            // 2. Llenar el input
            $('#new_user_pass').val(pass);

            // 3. Trigger automático
            showNotification('Generando y enviando...', 'success');
            setTimeout(() => {
                $('#btn_reset_pass').click();
            }, 500);
        });

        // --- ACCIÓN: BLOQUEAR / DESBLOQUEAR ---
        $(document).on('click', '#toggle_block_user', function() {
            let toggle = $(this);
            let isBlocked = toggle.hasClass('active'); 
            let actionValue = isBlocked ? 0 : 1; 

            $.post(ajaxUrl, { action: 'gptwp_quick_action', type: 'block', user_id: currentEditingUser, value: actionValue }, function(res) {
                if(res.success) {
                    if(actionValue === 1) {
                        toggle.addClass('active');
                        $('#block_status_label').text('BLOQUEADO').removeClass('is-active').addClass('is-blocked');
                        showNotification('Usuario bloqueado');
                    } else {
                        toggle.removeClass('active');
                        $('#block_status_label').text('ACTIVO').removeClass('is-blocked').addClass('is-active');
                        showNotification('Usuario desbloqueado');
                    }
                }
            });
        });

        // --- ACCIÓN: INSCRIBIR (ENROLL) ---
        $(document).on('click', '.btn-enroll', function() {
            let btn = $(this);
            let courseId = btn.data('course');
            btn.text('Procesando...');

            $.post(ajaxUrl, { action: 'gptwp_quick_action', type: 'enroll', user_id: currentEditingUser, value: courseId }, function(res) {
                if(res.success) {
                    $('.btn-manage-user[data-id="'+currentEditingUser+'"]').click();
                    showNotification('Usuario inscrito correctamente');
                }
            });
        });

        // --- ACCIÓN: RETIRAR ACCESO (REVOKE) ---
        $(document).on('click', '.btn-revoke', function() {
            if(!confirm('¿Seguro que quieres quitar el acceso a este curso?')) return;
            let btn = $(this);
            let courseId = btn.data('course');
            
            $.post(ajaxUrl, { action: 'gptwp_quick_action', type: 'revoke', user_id: currentEditingUser, value: courseId }, function(res) {
                if(res.success) {
                    $('.btn-manage-user[data-id="'+currentEditingUser+'"]').click();
                    showNotification('Acceso retirado');
                }
            });
        });

        // --- ACCIÓN: GUARDAR PERMISOS LECCIONES/TOPICS (BULK) ---
        $('#btn_save_permissions').click(function() {
            let btn = $(this);
            btn.prop('disabled', true).text('GUARDANDO...');
            
            let permissionsData = [];
            $('.gptwp-course-block').each(function() {
                let courseId = $(this).data('course-id');
                if($(this).find('.gptwp-lessons-area').length > 0) {
                    let checkedItems = [];
                    $(this).find('input[type="checkbox"]:checked').each(function() { 
                        checkedItems.push($(this).val()); 
                    });
                    permissionsData.push({ course_id: courseId, lessons: checkedItems });
                }
            });

            $.post(ajaxUrl, { action: 'gptwp_admin_save_permissions_bulk', user_id: currentEditingUser, data: permissionsData }, function(res) {
                btn.prop('disabled', false).text('GUARDADO CON ÉXITO');
                showNotification('Permisos guardados correctamente');
                setTimeout(() => { btn.text('GUARDAR PERMISOS DE CONTENIDO'); modal.removeClass('is-visible'); loadUsers('', currentPage); }, 1500);
            });
        });
    });
    </script>
    <?php
    return ob_get_clean();
});

/* -----------------------------------------------------------
   BACKEND: FUNCIONES AJAX Y LÓGICA
----------------------------------------------------------- */

// 1. LISTADO DE USUARIOS CON PAGINACIÓN

add_action('wp_ajax_gptwp_crm_get_users_v2', function() {
    if (!current_user_can('edit_users')) wp_send_json_error();

    $term = isset($_POST['term']) ? sanitize_text_field($_POST['term']) : '';
    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $per_page = 20; // Usuarios por página
    $offset = ($page - 1) * $per_page;

    $args = [
        'number' => $per_page, 
        'offset' => $offset,
        'orderby' => 'registered', 
        'order' => 'DESC',
        'count_total' => true // Importante para calcular paginación
    ];

    if(!empty($term)) {
        $args['search'] = "*{$term}*";
        $args['search_columns'] = ['user_login', 'user_email', 'display_name'];
    }

    // Consulta de usuarios
    $user_query = new WP_User_Query($args);
    $users = $user_query->get_results();
    $total_users = $user_query->get_total();
    $total_pages = ceil($total_users / $per_page);

    ob_start();
    if(!empty($users)) {
        foreach($users as $u) {
            $is_blocked = get_user_meta($u->ID, 'gptwp_is_blocked', true);
            $status_html = $is_blocked 
                ? '<span style="color:#ff4d4d; font-weight:bold;">⛔ BLOQUEADO</span>' 
                : '<span style="color:#4dff88;">✓ Activo</span>';

            echo '<tr>
                <td><img src="'.esc_url(get_avatar_url($u->ID)).'" class="gptwp-avatar-img"></td>
                <td><strong>'.esc_html($u->display_name).'</strong><br><small style="color:#666;">ID: '.$u->ID.'</small></td>
                <td>'.esc_html($u->user_email).'</td>
                <td>'.$status_html.'</td>
                <td style="text-align:right;">
                    <button class="btn-manage-user" data-id="'.$u->ID.'">GESTIONAR</button>
                </td>
            </tr>';
        }
    } else {
        echo '<tr><td colspan="5" style="text-align:center;">No se encontraron usuarios.</td></tr>';
    }
    $html_content = ob_get_clean();

    // Devolvemos HTML y datos de paginación
    wp_send_json_success([
        'html' => $html_content,
        'pagination' => [
            'current' => $page,
            'total' => $total_users,
            'pages' => $total_pages
        ]
    ]);
});

// 2. ACTUALIZAR PERFIL (NOMBRE Y EMAIL)
add_action('wp_ajax_gptwp_update_profile_data', function() {
    if (!current_user_can('edit_users')) wp_send_json_error();
    
    $user_id = intval($_POST['user_id']);
    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);

    if(email_exists($email) && email_exists($email) != $user_id) {
        wp_send_json_error('El correo ya está en uso por otro usuario.');
    }

    $args = [
        'ID' => $user_id,
        'display_name' => $name,
        'user_email' => $email
    ];
    
    $user_id = wp_update_user($args);

    if (is_wp_error($user_id)) wp_send_json_error($user_id->get_error_message());
    else wp_send_json_success();
});

// 3. CARGAR PERFIL COMPLETO (MODAL)
add_action('wp_ajax_gptwp_crm_load_full_profile', function() {
    if (!current_user_can('edit_users')) wp_send_json_error();
    
    $user_id = intval($_POST['user_id']);
    $user = get_userdata($user_id);
    
    // Bloqueo
    $is_blocked = get_user_meta($user_id, 'gptwp_is_blocked', true);
    $block_class = $is_blocked ? 'active' : '';
    $status_text = $is_blocked ? 'BLOQUEADO' : 'ACTIVO';
    $status_color = $is_blocked ? 'is-blocked' : 'is-active';

    // Cursos
    $all_courses = get_posts(['post_type' => 'sfwd-courses', 'numberposts' => -1, 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC']);

    ob_start();
    ?>
    
    <!-- HEADER EDITABLE (CON TOGGLE) -->
    <div class="profile-edit-row">
        <img src="<?php echo get_avatar_url($user_id); ?>" style="width:60px; height:60px; border-radius:50%; border:3px solid #333;">
        <div class="profile-inputs">
            <!-- INPUTS DESHABILITADOS POR DEFECTO -->
            <input type="text" id="edit_display_name" class="profile-input-field" value="<?php echo esc_attr($user->display_name); ?>" placeholder="Nombre Completo" disabled>
            <input type="email" id="edit_user_email" class="profile-input-field" value="<?php echo esc_attr($user->user_email); ?>" placeholder="Correo Electrónico" disabled>
        </div>
        
        <!-- BOTÓN EDITAR -->
        <button id="btn_enable_edit_profile" class="btn-enable-edit"><span class="dashicons dashicons-edit"></span> Editar Datos</button>
        <!-- BOTÓN GUARDAR (OCULTO INICIALMENTE) -->
        <button id="btn_update_profile" class="btn-update-profile">Guardar Cambios</button>
    </div>

    <!-- PANEL DE CONTROL (SEGURIDAD) -->
    <div class="user-control-panel">
        <div class="control-group">
            <label>Acceso a la Academia</label>
            <div class="switch-block">
                <div id="toggle_block_user" class="switch-toggle <?php echo $block_class; ?>"></div>
                <span id="block_status_label" class="block-status-text <?php echo $status_color; ?>"><?php echo $status_text; ?></span>
            </div>
        </div>
        <div class="control-group" style="grid-column: span 2;">
            <label>Cambio Rápido de Contraseña</label>
            <div class="password-input-group">
                <input type="text" id="new_user_pass" placeholder="Nueva contraseña...">
                <button id="btn_generate_pass" class="btn-mini-action" style="background:var(--accent-gold); color:#000; font-weight:bold;">Generar y Enviar</button>
                <button id="btn_reset_pass" class="btn-mini-action">Actualizar</button>
            </div>
        </div>
    </div>

    <h4 style="color:#F9B137; border-bottom:1px solid #333; padding-bottom:10px; margin-bottom:20px;">Gestión de Cursos y Permisos</h4>

    <!-- LISTADO DE CURSOS -->
    <?php
    foreach($all_courses as $course) {
        $c_id = $course->ID;
        $is_enrolled = sfwd_lms_has_access($c_id, $user_id);

        echo '<div class="gptwp-course-block" data-course-id="'.$c_id.'">';
        
        // Header del Curso
        echo '<div class="gptwp-course-header">';
        echo '<div class="course-name"><span class="dashicons dashicons-book"></span> ' . get_the_title($c_id);
        if($is_enrolled) echo '<span class="course-status-badge badge-enrolled">Inscrito</span>';
        else echo '<span class="course-status-badge badge-not-enrolled">No Inscrito</span>';
        echo '</div>'; // Fin name

        // Botones de Acción
        echo '<div class="course-actions">';
        if($is_enrolled) echo '<button class="btn-revoke" data-course="'.$c_id.'">Quitar Acceso</button>';
        else echo '<button class="btn-enroll" data-course="'.$c_id.'">Dar Acceso</button>';
        echo '</div></div>'; // Fin header

        // Si está inscrito, mostramos las lecciones y topics
        if($is_enrolled) {
            $enabled_modules = get_user_meta($user_id, 'enabled_modules_' . $c_id, true);
            if(!is_array($enabled_modules)) $enabled_modules = [];
            
            $lessons = learndash_get_course_lessons_list($c_id);

            echo '<div class="gptwp-lessons-area is-open">';
            if(empty($lessons)) {
                echo '<p style="font-size:12px; color:#666;">Este curso no tiene lecciones.</p>';
            } else {
                echo '<div style="margin-bottom:10px; font-size:11px; color:#888;">SELECCIONA QUÉ LECCIONES Y CLASES (TOPICS) PUEDE VER EL ESTUDIANTE:</div>';
                echo '<div class="gptwp-lessons-grid">';
                
                foreach($lessons as $l) {
                    $l_id = $l['post']->ID;
                    $l_title = get_the_title($l_id);
                    
                    // --- FILTRO: Omitir si el título contiene "Separador" (ej: Separador (Titulo:...)) ---
                    if (stripos($l_title, 'Separador') !== false) {
                        continue;
                    }

                    $val_l = 'lesson_' . $l_id;
                    $checked_l = in_array($val_l, $enabled_modules) ? 'checked' : '';
                    
                    // Render Lección
                    echo '<div class="lesson-group">';
                    echo '<div class="lesson-main-check">';
                    echo '<input type="checkbox" id="chk_'.$l_id.'" class="lesson-check-input" value="'.$val_l.'" '.$checked_l.'>';
                    echo '<label for="chk_'.$l_id.'" class="lesson-check-label">'.$l_title.'</label>';
                    echo '</div>';

                    // Obtener Topics (Clases) de la lección
                    $topics = learndash_get_topic_list($l_id, $c_id);
                    if(!empty($topics)) {
                        echo '<div class="topic-list">';
                        foreach($topics as $t) {
                            $t_id = $t->ID;
                            $t_title = get_the_title($t_id);
                            // Opcional: Filtro también para topics si fuera necesario
                            // if (stripos($t_title, '(Separador)') !== false) continue;

                            $val_t = 'topic_' . $t_id;
                            $checked_t = in_array($val_t, $enabled_modules) ? 'checked' : '';

                            echo '<div>';
                            echo '<input type="checkbox" id="chk_t_'.$t_id.'" class="lesson-check-input" style="width:14px; height:14px;" value="'.$val_t.'" '.$checked_t.'>';
                            echo '<label for="chk_t_'.$t_id.'" class="topic-check-label"> '.$t_title.'</label>';
                            echo '</div>';
                        }
                        echo '</div>';
                    }

                    echo '</div>'; // fin lesson-group
                }
                echo '</div>';
            }
            echo '</div>';
        }
        echo '</div>'; // Fin course block
    }

    wp_send_json_success(ob_get_clean());
});

// 4. ACCIONES RÁPIDAS (CON ENVÍO DE EMAIL EN PASS)
add_action('wp_ajax_gptwp_quick_action', function() {
    if (!current_user_can('edit_users')) wp_send_json_error();

    $type = $_POST['type'];
    $user_id = intval($_POST['user_id']);
    $value = $_POST['value']; 

    switch($type) {
        case 'password':
            // 1. Cambiamos la contraseña
            wp_set_password($value, $user_id);

            // 2. Preparamos el correo (HTML)
            $user_info = get_userdata($user_id);
            if ($user_info) {
                $email_data = [
                    'name'        => $user_info->display_name,
                    'username'    => $user_info->user_login, 
                    'password'    => $value,
                    'is_new_user' => true, // Para mostrar la caja de credenciales
                    'login_url'   => 'https://academia.cdibusinessschool.com/'
                ];

                $subject = 'Nuevas Credenciales de Acceso - ' . get_bloginfo('name');
                $message = gptwp_get_email_template($email_data);
                
                // Enviamos el correo (HTML)
                $headers = array('Content-Type: text/html; charset=UTF-8');
                $sent = wp_mail($user_info->user_email, $subject, $message, $headers);
                
                if(!$sent) {
                    wp_send_json_error('Contraseña cambiada, pero falló el envío del correo (Error Servidor).');
                }
            }
            break;
            
        case 'block':
            if($value == 1) update_user_meta($user_id, 'gptwp_is_blocked', 1);
            else delete_user_meta($user_id, 'gptwp_is_blocked');
            break;
        case 'enroll':
            ld_update_course_access($user_id, intval($value), false);
            break;
        case 'revoke':
            ld_update_course_access($user_id, intval($value), true);
            delete_user_meta($user_id, 'enabled_modules_' . intval($value));
            break;
    }
    wp_send_json_success();
});

// 5. GUARDAR PERMISOS MASIVOS (Ahora incluye topics)
add_action('wp_ajax_gptwp_admin_save_permissions_bulk', function() {
    if (!current_user_can('edit_users')) wp_send_json_error();
    $user_id = intval($_POST['user_id']);
    $data_array = isset($_POST['data']) ? $_POST['data'] : [];

    if($user_id > 0 && is_array($data_array)) {
        foreach($data_array as $item) {
            $c_id = intval($item['course_id']);
            $items = isset($item['lessons']) ? $item['lessons'] : []; // Array mixto lessons/topics
            update_user_meta($user_id, 'enabled_modules_' . $c_id, $items);
        }
        wp_send_json_success();
    }
    wp_send_json_error();
});

// 6. HOOK CRÍTICO: BLOQUEO LOGIN
add_filter('wp_authenticate_user', function($user) {
    if (is_wp_error($user)) return $user;
    if (get_user_meta($user->ID, 'gptwp_is_blocked', true)) {
        return new WP_Error('blocked_account', '<strong>ACCESO DENEGADO:</strong> Tu cuenta ha sido suspendida.');
    }
    return $user;
}, 10, 1);


/* -------------------------------------------------------------------------- */
/*                        MÓDULO DE REGISTRO & IMPORTACIÓN                    */
/* -------------------------------------------------------------------------- */

/* ==========================================================================
   AJAX: OBTENER LECCIONES (Para el registro)
   ========================================================================== */
add_action('wp_ajax_gptwp_get_course_steps', function() {
    if (!is_user_logged_in() || !current_user_can('manage_options') && !current_user_can('profesor')) wp_send_json_error();
    $course_id = isset($_POST['course_id']) ? absint($_POST['course_id']) : 0;
    $lessons_raw = function_exists('learndash_get_course_lessons_list') ? learndash_get_course_lessons_list($course_id) : [];
    
    ob_start();
    foreach ($lessons_raw as $lesson_data) {
        $lesson = $lesson_data['post'];
        if (stripos($lesson->post_title, 'separador') === 0) continue;
        ?>
        <div class="gptwp-lesson-row">
            <label class="gptwp-lesson-label">
                <input type="checkbox" name="enabled[<?php echo $course_id; ?>][]" value="lesson_<?php echo $lesson->ID; ?>">
                <span><?php echo esc_html($lesson->post_title); ?></span>
            </label>
        </div>
        <?php
    }
    wp_send_json_success(['html' => ob_get_clean()]);
});

/* ==========================================================================
   SHORTCODE 2: REGISTRO INDIVIDUAL (FORMULARIO FLUIDO + CURVO)
   Uso: [gptwp_registro_estudiante]
   ========================================================================== */
add_shortcode('gptwp_registro_estudiante', function() {
    if (!current_user_can('manage_options') && !current_user_can('profesor')) return '<p style="color:#F9B137;">Acceso Denegado.</p>';

    $feedback_html = ''; 

    // -- PROCESAMIENTO PHP (INTACTO) --
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && wp_verify_nonce($_POST['gptwp_registrar_nonce'], 'gptwp_registrar')) {
        $email = sanitize_email($_POST['email']);
        $first_name = sanitize_text_field($_POST['first_name']);
        $last_name = sanitize_text_field($_POST['last_name']);
        $selected_courses = $_POST['courses'] ?? [];

        if (!is_email($email)) {
            $feedback_html = '<div class="gptwp-msg error">Error: Email inválido.</div>';
        } elseif (empty($selected_courses)) {
            $feedback_html = '<div class="gptwp-msg error">Error: Selecciona un curso.</div>';
        } else {
            $user = get_user_by('email', $email);
            $is_new = false;
            $pass = '';
            $username = '';
            $user_created_success = true;

            if (!$user) {
                $pass = wp_generate_password(10, false);
                $username_base = sanitize_user(strtolower($first_name . '.' . $last_name), true);
                if(empty($username_base)) $username_base = sanitize_user(current(explode('@', $email)));
                $username = $username_base;
                $i = 1;
                while (username_exists($username)) { $username = $username_base . '.' . $i; $i++; }
                
                $user_id = wp_create_user($username, $pass, $email);
                
                if (is_wp_error($user_id)) {
                    $feedback_html = '<div class="gptwp-msg error">Error: ' . $user_id->get_error_message() . '</div>';
                    $user_created_success = false;
                } else {
                    wp_update_user(['ID' => $user_id, 'first_name' => $first_name, 'last_name' => $last_name, 'role' => 'subscriber']);
                    update_user_meta($user_id, 'show_admin_bar_front', 'false');
                    $is_new = true;
                    $user = get_userdata($user_id);
                }
            } else {
                $user_id = $user->ID;
                $username = $user->user_login;
                if(empty($user->first_name)) wp_update_user(['ID'=>$user_id, 'first_name'=>$first_name, 'last_name'=>$last_name]);
            }

            if ($user_created_success) {
                foreach ($selected_courses as $cid_raw) {
                    $cid = (int)$cid_raw;
                    if ($cid > 0) {
                        ld_update_course_access($user_id, $cid, false);
                        if (function_exists('learndash_delete_user_progress_transients')) learndash_delete_user_progress_transients($user_id);
                        $enrolled_courses = learndash_user_get_enrolled_courses($user_id, true);
                        if(!in_array($cid, $enrolled_courses)) {
                            $enrolled_courses[] = $cid;
                            learndash_user_set_enrolled_courses($user_id, $enrolled_courses);
                        }
                    }
                    if(isset($_POST['enabled'][$cid])) update_user_meta($user_id, 'enabled_modules_'.$cid, $_POST['enabled'][$cid]);
                }

                if (function_exists('gptwp_get_email_template')) {
                    $email_data = ['name' => $first_name, 'is_new_user' => $is_new, 'username' => $username, 'password' => $pass, 'login_url' => wp_login_url()];
                    $headers = ['Content-Type: text/html; charset=UTF-8'];
                    $subject = $is_new ? "Acceso CDI Business School" : "Actualización de Contenidos";
                    wp_mail($email, $subject, gptwp_get_email_template($email_data), $headers);
                }

                $feedback_html = $is_new 
                    ? '<div class="gptwp-msg success">Usuario <b>'.$username.'</b> creado y matriculado.</div>' 
                    : '<div class="gptwp-msg warning">Usuario <b>'.$username.'</b> actualizado con nuevos cursos.</div>';
            }
        }
    }

    $courses = get_posts(['post_type'=>'sfwd-courses','posts_per_page'=>-1, 'post_status' => 'publish']);
    
    ob_start();
    ?>
    <div class="gptwp-fluid-form">
        <?php echo $feedback_html; ?>

        <form method="post">
            <?php wp_nonce_field('gptwp_registrar','gptwp_registrar_nonce'); ?>
            <h2 class="gptwp-form-head">REGISTRO INDIVIDUAL</h2>
            
            <div class="gptwp-row-2">
                <input type="text" name="first_name" placeholder="Nombre" required class="gptwp-input">
                <input type="text" name="last_name" placeholder="Apellido" required class="gptwp-input">
            </div>
            
            <input type="email" name="email" placeholder="Correo electrónico" required class="gptwp-input full-width">
            
            <div class="gptwp-section-label">SELECCIONAR CURSOS:</div>
            
            <div class="gptwp-check-grid">
                <?php foreach($courses as $c): ?>
                    <label class="gptwp-check-card">
                        <input type="checkbox" name="courses[]" value="<?php echo $c->ID; ?>" class="course-check-trigger"> 
                        <span><?php echo esc_html($c->post_title); ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
            
            <div id="steps-area-dynamic"></div>
            
            <button type="submit" class="gptwp-btn-submit">REGISTRAR ESTUDIANTE</button>
        </form>
    </div>

    <script>
    document.querySelectorAll('.course-check-trigger').forEach(check => {
        check.addEventListener('change', function() {
            if(this.checked) {
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                    body: 'action=gptwp_get_course_steps&course_id=' + this.value
                }).then(r => r.json()).then(res => {
                    const div = document.createElement('div');
                    div.id = 'steps-container-' + this.value;
                    div.style.marginBottom = "20px";
                    div.innerHTML = `<div style="color:#F9B137; font-weight:bold; margin-bottom:10px; font-size:13px; text-transform:uppercase;">${this.nextElementSibling.textContent} - Módulos:</div>` + res.data.html;
                    document.getElementById('steps-area-dynamic').appendChild(div);
                });
            } else { 
                const el = document.getElementById('steps-container-' + this.value);
                if(el) el.remove(); 
            }
        });
    });
    </script>
    <?php
    return ob_get_clean();
});

/* ==========================================================================
   SHORTCODE 3: IMPORTADOR CSV (FLUIDO + CURVO)
   Uso: [gptwp_importador_masivo]
   ========================================================================== */
add_shortcode('gptwp_importador_masivo', function() {
    if (!current_user_can('administrator')) return '<p style="color:#F9B137;">Acceso Denegado.</p>';

    $feedback_output = '';
    
    if (isset($_POST['gptwp_csv_nonce']) && wp_verify_nonce($_POST['gptwp_csv_nonce'], 'gptwp_csv_import')) {
        if (!empty($_FILES['csv_file']['tmp_name'])) {
            $course_id = intval($_POST['course_id']);
            $file = fopen($_FILES['csv_file']['tmp_name'], 'r');
            
            $count_new = 0; $count_updated = 0; $errors = [];

            while (($row = fgetcsv($file)) !== FALSE) {
                $email = sanitize_email(trim($row[0] ?? ''));
                $first_name = sanitize_text_field(trim($row[1] ?? ''));
                $last_name = sanitize_text_field(trim($row[2] ?? ''));

                if (!is_email($email)) continue; 

                $user = get_user_by('email', $email);
                $is_new = false; $pass = ''; $username = '';

                if (!$user) {
                    $pass = wp_generate_password(10, false);
                    $username_base = sanitize_user(strtolower($first_name . '.' . $last_name), true);
                    if(empty($username_base)) $username_base = sanitize_user(current(explode('@', $email)));
                    $username = $username_base;
                    $i = 1;
                    while (username_exists($username)) { $username = $username_base . '.' . $i; $i++; }

                    $user_id = wp_create_user($username, $pass, $email);
                    if (is_wp_error($user_id)) { $errors[] = "Error $email: ".$user_id->get_error_message(); continue; }
                    
                    wp_update_user(['ID' => $user_id, 'first_name' => $first_name, 'last_name' => $last_name, 'role' => 'subscriber']);
                    update_user_meta($user_id, 'show_admin_bar_front', 'false');
                    $is_new = true;
                    $user = get_userdata($user_id);
                    $count_new++;
                } else {
                    $user_id = $user->ID;
                    $username = $user->user_login;
                    if(empty($user->first_name)) wp_update_user(['ID'=>$user_id, 'first_name'=>$first_name, 'last_name'=>$last_name]);
                    $count_updated++;
                }

                if ($course_id > 0) {
                    ld_update_course_access($user_id, $course_id, false);
                    if (function_exists('learndash_delete_user_progress_transients')) learndash_delete_user_progress_transients($user_id);
                    $enrolled_courses = learndash_user_get_enrolled_courses($user_id, true);
                    if(!in_array($course_id, $enrolled_courses)) {
                        $enrolled_courses[] = $course_id;
                        learndash_user_set_enrolled_courses($user_id, $enrolled_courses);
                    }
                }

                if (function_exists('gptwp_get_email_template')) {
                    $email_data = ['name' => $first_name ?: $username, 'is_new_user' => $is_new, 'username' => $username, 'password' => $pass, 'login_url' => wp_login_url()];
                    $headers = ['Content-Type: text/html; charset=UTF-8'];
                    $subject = "Acceso a Grabaciones CDI Business School"; 
                    wp_mail($email, $subject, gptwp_get_email_template($email_data), $headers);
                }
            }
            fclose($file);

            $feedback_output .= '<div class="gptwp-msg success"><strong>Proceso Terminado:</strong><br>Nuevos: '.$count_new.' | Actualizados: '.$count_updated.'</div>';
            if (!empty($errors)) $feedback_output .= '<div class="gptwp-msg error">'.implode('<br>', $errors).'</div>';
            
        } else {
             $feedback_output = '<div class="gptwp-msg error">Error: Falta el archivo CSV.</div>';
        }
    }

    $courses = get_posts(['post_type'=>'sfwd-courses','posts_per_page'=>-1, 'post_status' => 'publish']);
    
    ob_start();
    ?>
    <div class="gptwp-fluid-form">
        <h2 class="gptwp-form-head">Importación Masiva</h2>
        <?php echo $feedback_output; ?>

        <div style="background:#1f1f1f; padding:15px; border-left:3px solid #F9B137; margin-bottom:25px; font-size:13px; color:#ccc; border-radius:8px;">
            <strong>Formato CSV (sin encabezados):</strong> A: Email | B: Nombre | C: Apellido
        </div>

        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('gptwp_csv_import', 'gptwp_csv_nonce'); ?>
            
            <div style="margin-bottom:20px;">
                <label style="display:block; color:#F9B137; font-weight:800; margin-bottom:10px; font-size:12px; text-transform:uppercase;">1. Selecciona el Curso:</label>
                <select name="course_id" required class="gptwp-input">
                    <option value="">-- Elegir Curso --</option>
                    <?php foreach($courses as $c): ?>
                        <option value="<?php echo $c->ID; ?>"><?php echo esc_html($c->post_title); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div style="margin-bottom:30px;">
                <label style="display:block; color:#F9B137; font-weight:800; margin-bottom:10px; font-size:12px; text-transform:uppercase;">2. Sube el archivo CSV:</label>
                <input type="file" name="csv_file" accept=".csv" required class="gptwp-input">
            </div>

            <button type="submit" class="gptwp-btn-submit">PROCESAR IMPORTACIÓN</button>
        </form>
    </div>
    <?php
    return ob_get_clean();
});

// 8. SHORTCODES: KPIs ESTUDIANTES (INDIVIDUALES)
// Uso: [kpi_total_alumnos], [kpi_nuevos_alumnos], [kpi_total_profesores]

add_shortcode('kpi_total_alumnos', function() {
    if (!current_user_can('administrator') && !current_user_can('shop_manager')) return '0';
    $total_users = count_users();
    return $total_users['avail_roles']['subscriber'] ?? 0;
});

add_shortcode('kpi_nuevos_alumnos', function() {
    if (!current_user_can('administrator') && !current_user_can('shop_manager')) return '0';
    $args_month = [
        'role' => 'subscriber',
        'date_query' => [['year' => date('Y'), 'month' => date('m')]],
        'fields' => 'ID'
    ];
    $query_month = new WP_User_Query($args_month);
    return $query_month->get_total();
});

add_shortcode('kpi_total_profesores', function() {
    if (!current_user_can('administrator') && !current_user_can('shop_manager')) return '0';
    $total_users = count_users();
    return $total_users['avail_roles']['profesor'] ?? 0;
});


/* -------------------------------------------------------------------------- */
/*                        MÓDULO 3: MOTOR FINANCIERO                          */
/* -------------------------------------------------------------------------- */

// 1. INICIALIZACIÓN DE LA BASE DE DATOS (TABLA FINANCIERA)
add_action('init', 'gptwp_finance_db_init');

function gptwp_finance_db_init() {
    global $wpdb;
    $tabla = $wpdb->prefix . 'gptwp_finance';
    
    // Si no existe, la creamos
    if($wpdb->get_var("SHOW TABLES LIKE '$tabla'") != $tabla) {
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $tabla (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) UNSIGNED NOT NULL,
            order_id bigint(20) UNSIGNED DEFAULT 0, /* 0 si es manual */
            concept varchar(255) NOT NULL,          /* Ej: Cuota 1 Curso Trading */
            amount decimal(10,2) NOT NULL,
            currency varchar(10) DEFAULT 'EUR' NOT NULL,
            gateway varchar(50) NOT NULL,           /* stripe, paypal, cash, transfer */
            status varchar(20) NOT NULL,            /* paid, pending, overdue, refunded */
            due_date datetime DEFAULT NULL,         /* Para cuotas futuras */
            paid_date datetime DEFAULT NULL,        /* Fecha real del pago */
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
}

// 2. FUNCIÓN MAESTRA PARA REGISTRAR TRANSACCIONES (API INTERNA)
function gptwp_registrar_finanza($datos) {
    global $wpdb;
    $tabla = $wpdb->prefix . 'gptwp_finance';
    
    $defaults = [
        'user_id' => 0,
        'order_id' => 0,
        'concept' => 'Pago General',
        'amount' => 0.00,
        'currency' => 'EUR',
        'gateway' => 'manual',
        'status' => 'pending', // paid, pending, overdue
        'due_date' => null,
        'paid_date' => null
    ];
    
    $args = wp_parse_args($datos, $defaults);
    
    // Si el estado es 'paid' y no hay fecha de pago, poner AHORA
    if ($args['status'] === 'paid' && empty($args['paid_date'])) {
        $args['paid_date'] = current_time('mysql');
    }

    return $wpdb->insert($tabla, $args);
}

// 3. AUTOMATIZACIÓN: ESCUCHAR A WOOCOMMERCE
// Se dispara cuando un pedido pasa a "Completado"

add_action('woocommerce_order_status_completed', 'gptwp_capturar_pago_woo');

function gptwp_capturar_pago_woo($order_id) {
    // Evitar duplicados
    if (get_post_meta($order_id, '_gptwp_finance_recorded', true)) return;

    $order = wc_get_order($order_id);
    $user_id = $order->get_user_id();
    
    // Si fue compra de invitado, intentamos buscar por email o no registramos en historial de alumno
    if (!$user_id) {
        $email = $order->get_billing_email();
        $user = get_user_by('email', $email);
        if ($user) $user_id = $user->ID;
    }

    if ($user_id) {
        // Datos del pedido
        $total = $order->get_total();
        $currency = $order->get_currency();
        $payment_method = $order->get_payment_method(); // ej: stripe, bacs, cod
        
        // Construir concepto (Lista de productos)
        $items = $order->get_items();
        $concept_parts = [];
        foreach ($items as $item) {
            $concept_parts[] = $item->get_name();
        }
        $concepto = implode(', ', $concept_parts);
        if (strlen($concepto) > 200) $concepto = substr($concepto, 0, 197) . '...';

        // Registrar en nuestra tabla financiera
        gptwp_registrar_finanza([
            'user_id'   => $user_id,
            'order_id'  => $order_id,
            'concept'   => $concepto,
            'amount'    => $total,
            'currency'  => $currency,
            'gateway'   => $payment_method,
            'status'    => 'paid',
            'paid_date' => current_time('mysql')
        ]);

        // Marcar como procesado para no duplicar
        update_post_meta($order_id, '_gptwp_finance_recorded', 'yes');
        
        // Opcional: Enviar notificación al estudiante (usando tu módulo anterior)
        if (function_exists('gptwp_crear_notificacion')) {
            gptwp_crear_notificacion(
                $user_id, 
                'Pago Recibido', 
                'Hemos registrado tu pago de ' . $total . ' ' . $currency . ' correctamente.',
                'system'
            );
        }
    }
}

// 4. SHORTCODE ADMIN: REGISTRO MANUAL DE PAGOS (EFECTIVO/TRANSFERENCIA)
// Uso: [admin_registrar_pago]

add_shortcode('admin_registrar_pago', function() {
    if (!current_user_can('manage_options') && !current_user_can('shop_manager')) return '';
    
    wp_enqueue_script('jquery');
    
    // Procesar Formulario
    $msg = '';
    if (isset($_POST['gptwp_manual_pay_nonce']) && wp_verify_nonce($_POST['gptwp_manual_pay_nonce'], 'save_manual_pay')) {
        $uid = intval($_POST['user_id']);
        $amount = floatval($_POST['amount']);
        $concept = sanitize_text_field($_POST['concept']);
        $gateway = sanitize_text_field($_POST['gateway']);
        
        if ($uid > 0 && $amount > 0) {
            gptwp_registrar_finanza([
                'user_id' => $uid,
                'concept' => $concept,
                'amount' => $amount,
                'gateway' => $gateway, // cash, transfer
                'status' => 'paid'
            ]);
            $msg = '<div style="background:#4dff88; color:#000; padding:10px; border-radius:5px; margin-bottom:15px;">Pago registrado correctamente.</div>';
            
            // Notificar
            if (function_exists('gptwp_crear_notificacion')) {
                gptwp_crear_notificacion($uid, 'Pago Registrado', "Pago manual de $$amount recibido ($concept).", 'system');
            }
        } else {
            $msg = '<div style="background:#ff4d4d; color:#fff; padding:10px; border-radius:5px; margin-bottom:15px;">Error: Faltan datos.</div>';
        }
    }

    ob_start();
    ?>
    <div class="gptwp-finance-form-wrapper" style="background:#141414; padding:25px; border-radius:12px; border:1px solid #333; color:#fff; max-width:500px;">
        <h3 style="color:#F9B137; margin-top:0; border-bottom:1px solid #333; padding-bottom:15px;">Registrar Pago Manual</h3>
        <?php echo $msg; ?>
        
        <form method="post">
            <?php wp_nonce_field('save_manual_pay', 'gptwp_manual_pay_nonce'); ?>
            
            <!-- Buscador de Usuario (Reutilizamos estilos si existen, si no, input simple) -->
            <div style="margin-bottom:15px;">
                <label style="display:block; font-size:12px; color:#888; margin-bottom:5px;">ID ESTUDIANTE (User ID)</label>
                <input type="number" name="user_id" required style="width:100%; background:#0a0a0a; border:1px solid #333; color:#fff; padding:10px; border-radius:6px;">
                <small style="color:#666;">Puedes ver el ID en la tabla de estudiantes.</small>
            </div>

            <div style="margin-bottom:15px;">
                <label style="display:block; font-size:12px; color:#888; margin-bottom:5px;">CONCEPTO</label>
                <input type="text" name="concept" placeholder="Ej: Mensualidad Enero - Efectivo" required style="width:100%; background:#0a0a0a; border:1px solid #333; color:#fff; padding:10px; border-radius:6px;">
            </div>

            <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px; margin-bottom:20px;">
                <div>
                    <label style="display:block; font-size:12px; color:#888; margin-bottom:5px;">MONTO (€)</label>
                    <input type="number" step="0.01" name="amount" required style="width:100%; background:#0a0a0a; border:1px solid #333; color:#fff; padding:10px; border-radius:6px;">
                </div>
                <div>
                    <label style="display:block; font-size:12px; color:#888; margin-bottom:5px;">MÉTODO</label>
                    <select name="gateway" style="width:100%; background:#0a0a0a; border:1px solid #333; color:#fff; padding:10px; border-radius:6px;">
                        <option value="cash">Efectivo</option>
                        <option value="transfer">Transferencia</option>
                        <option value="crypto">Cripto</option>
                        <option value="other">Otro</option>
                    </select>
                </div>
            </div>

            <button type="submit" style="width:100%; background:#F9B137; color:#000; border:none; padding:12px; border-radius:50px; font-weight:800; cursor:pointer;">REGISTRAR INGRESO</button>
        </form>
    </div>

    <?php
    return ob_get_clean();
});

// ==============================================================================
// === MÓDULO 3: MOTOR FINANCIERO MODULAR (KPIs, Gráficas Reales y AJAX) ===
// ==============================================================================

// 1. CARGA DE LIBRERÍAS (Chart.js + Flatpickr para rangos de fecha)

add_action('wp_enqueue_scripts', function() {
    // Chart.js
    wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', [], '4.4.0', true);
    
    // Flatpickr (Calendario Rango)
    wp_enqueue_style('flatpickr-css', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css');
    wp_enqueue_style('flatpickr-dark', 'https://cdn.jsdelivr.net/npm/flatpickr/dist/themes/dark.css'); // Tema oscuro
    wp_enqueue_script('flatpickr-js', 'https://cdn.jsdelivr.net/npm/flatpickr', [], '4.6.13', true);
    // Idioma Español para Flatpickr
    wp_enqueue_script('flatpickr-es', 'https://npmcdn.com/flatpickr/dist/l10n/es.js', ['flatpickr-js'], null, true);
});

// 2. SHORTCODE: FILTROS DE FECHA (EL CEREBRO)
// Uso: [finanzas_filtros]

add_shortcode('finanzas_filtros', function() {
    if (!current_user_can('manage_options') && !current_user_can('shop_manager')) return '';
    ob_start();
    ?>
    <div class="gptwp-fin-controls-wrapper">
        <div class="gptwp-date-input-group">
            <span class="dashicons dashicons-calendar-alt input-icon"></span>
            <!-- Input único para rango, texto corto "Filtrar" -->
            <input type="text" id="fin_date_range" placeholder="Filtrar" readonly>
        </div>
    </div>
    
    <!-- ESTILOS Y SCRIPTS CENTRALIZADOS -->
    <!-- Script movido a dashboard-master para compatibilidad AJAX -->

    <?php return ob_get_clean();
});


// 3. SHORTCODE: GRÁFICA SOLA (TRANSPARENTE)
// Uso: [finanzas_grafica]
add_shortcode('finanzas_grafica', function() {
    if (!current_user_can('manage_options') && !current_user_can('shop_manager')) return '';
    return '<div class="gptwp-chart-container"><canvas id="financeChart"></canvas></div>';
});


// 4. SHORTCODES: KPIS (SIN CAMBIOS)

add_shortcode('kpi_ingresos_hoy', function() { return '<span id="val_ingreso_hoy" class="gptwp-kpi-value">€0.00</span>'; });
add_shortcode('kpi_facturado_rango', function() { return '<span id="val_ingreso_rango" class="gptwp-kpi-value">€0.00</span>'; });
add_shortcode('kpi_proyeccion_7d', function() { return '<span id="val_proyeccion" class="gptwp-kpi-value">€0.00</span>'; });
add_shortcode('kpi_cartera_vencida', function() { return '<span id="val_mora" class="gptwp-kpi-value text-red">€0.00</span>'; });


// 5. SHORTCODE: TABLA DE CARTERA (ESTILO TRANSPARENTE)
// Uso: [finanzas_tabla_cartera]

add_shortcode('finanzas_tabla_cartera', function() {
    if (!current_user_can('manage_options') && !current_user_can('shop_manager')) return '';
    ob_start();
    ?>
    <div class="gptwp-table-responsive">
        <table class="fin-table">
            <thead>
                <tr>
                    <th>Estudiante</th>
                    <th>Concepto</th>
                    <th>Monto</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody id="fin_cartera_body">
                <tr><td colspan="5" style="text-align:center; padding:30px; color:#666;">Cargando transacciones...</td></tr>
            </tbody>
        </table>
    </div>
    <?php return ob_get_clean();
});

// 6. AJAX HANDLER (CEREBRO DE DATOS)
add_action('wp_ajax_gptwp_get_finance_data', function() {
    if (!current_user_can('manage_options') && !current_user_can('shop_manager')) wp_send_json_error();
    
    global $wpdb;
    $tabla = $wpdb->prefix . 'gptwp_finance';
    
    $start = sanitize_text_field($_POST['start']) . ' 00:00:00';
    $end = sanitize_text_field($_POST['end']) . ' 23:59:59';
    $hoy_start = date('Y-m-d 00:00:00');
    $hoy_end = date('Y-m-d 23:59:59');

    // KPIs
    $kpi_hoy = $wpdb->get_var("SELECT SUM(amount) FROM $tabla WHERE status='paid' AND paid_date BETWEEN '$hoy_start' AND '$hoy_end'");
    $kpi_rango = $wpdb->get_var("SELECT SUM(amount) FROM $tabla WHERE status='paid' AND paid_date BETWEEN '$start' AND '$end'");
    $kpi_proyeccion = $wpdb->get_var("SELECT SUM(amount) FROM $tabla WHERE status='pending' AND due_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 7 DAY)");
    $kpi_mora = $wpdb->get_var("SELECT SUM(amount) FROM $tabla WHERE status='overdue' OR (status='pending' AND due_date < NOW())");

    // GRÁFICA
    $grafico_raw = $wpdb->get_results("
        SELECT DATE(paid_date) as fecha, SUM(amount) as total 
        FROM $tabla 
        WHERE status='paid' AND paid_date BETWEEN '$start' AND '$end' 
        GROUP BY DATE(paid_date) 
        ORDER BY fecha ASC
    ");
    
    $labels = []; $values = [];
    foreach($grafico_raw as $g) {
        $labels[] = date('d M', strtotime($g->fecha));
        $values[] = $g->total;
    }

    // TABLA
    $cartera = $wpdb->get_results("
        SELECT * FROM $tabla 
        WHERE (status IN ('pending', 'overdue')) 
        OR (status='paid' AND paid_date BETWEEN '$start' AND '$end')
        ORDER BY paid_date DESC, due_date ASC 
        LIMIT 50
    ");

    ob_start();
    if(empty($cartera)) {
        echo '<tr><td colspan="5" style="text-align:center; padding:30px; color:#666;">No hay datos en este periodo.</td></tr>';
    } else {
        foreach($cartera as $c) {
            $user = get_userdata($c->user_id);
            $name = $user ? $user->display_name : 'ID #'.$c->user_id;
            $avatar = get_avatar_url($c->user_id);
            
            $st_class = 'st-pending'; $st_text = 'Pendiente';
            if($c->status == 'paid') { $st_class = 'st-paid'; $st_text = 'Pagado'; }
            if($c->status == 'overdue' || ($c->status == 'pending' && strtotime($c->due_date) < time())) { $st_class = 'st-overdue'; $st_text = 'Vencido'; }
            
            $fecha = ($c->status == 'paid') ? $c->paid_date : $c->due_date;
            $fecha_fmt = date('d M Y', strtotime($fecha));

            echo "<tr>
                <td>
                    <div class='fin-user-row'>
                        <img src='$avatar' class='fin-user-av'> 
                        <span class='fin-user-name'>$name</span>
                    </div>
                </td>
                <td class='fin-concept'>".esc_html($c->concept)."</td>
                <td class='fin-amount'>€".number_format($c->amount, 2)."</td>
                <td style='color:#888;'>$fecha_fmt</td>
                <td><span class='fin-badge $st_class'>$st_text</span></td>
            </tr>";
        }
    }
    $html_tabla = ob_get_clean();

    wp_send_json_success([
        'kpi' => [
            'hoy' => '€' . number_format((float)$kpi_hoy, 2),
            'rango' => '€' . number_format((float)$kpi_rango, 2),
            'proyeccion' => '€' . number_format((float)$kpi_proyeccion, 2),
            'mora' => '€' . number_format((float)$kpi_mora, 2),
        ],
        'grafica' => [ 'labels' => $labels, 'values' => $values ],
        'html_tabla' => $html_tabla
    ]);
});



// ... existing code ...

// ==============================================================================
// 7. AJAX HANDLER: LAZY LOAD TABS
// ==============================================================================
add_action('wp_ajax_gptwp_load_dashboard_tab', function() {
    // Verificar nonce y permisos
    if (!current_user_can('administrator')) wp_send_json_error('Acceso denegado');
    // check_ajax_referer('gptwp_admin_nonce', 'nonce'); // Usar si se implementa wp_localize_script

    $tab = sanitize_text_field($_POST['tab']);
    $content = '';

    switch ($tab) {
        case 'tab-finanzas':
            ob_start();
            ?>
            <!-- Fila 1: Filtros -->
            <div style="margin-bottom: 20px;">
                <?php echo do_shortcode('[finanzas_filtros]'); ?>
            </div>

            <!-- Fila 2: KPIs (Grid 4 columnas) -->
            <div class="gptwp-kpi-grid">
                <div class="gptwp-kpi-card">
                    <small>Ingresos Hoy</small>
                    <?php echo do_shortcode('[kpi_ingresos_hoy]'); ?>
                </div>
                <div class="gptwp-kpi-card">
                    <small>Facturado (Rango)</small>
                    <?php echo do_shortcode('[kpi_facturado_rango]'); ?>
                </div>
                <div class="gptwp-kpi-card">
                    <small>Proyección (7d)</small>
                    <?php echo do_shortcode('[kpi_proyeccion_7d]'); ?>
                </div>
                <div class="gptwp-kpi-card">
                    <small>Cartera Vencida</small>
                    <?php echo do_shortcode('[kpi_cartera_vencida]'); ?>
                </div>
            </div>

            <!-- Fila 3: Gráfica -->
            <div class="gptwp-section-box">
                <h4 class="gptwp-box-title">Tendencia de Ingresos</h4>
                <?php echo do_shortcode('[finanzas_grafica]'); ?>
            </div>

            <!-- Fila 4: Registro Manual + Tabla Cartera -->
            <div class="gptwp-finance-split">
                <div class="gptwp-finance-form-area">
                    <?php echo do_shortcode('[admin_registrar_pago]'); ?>
                </div>
                <div class="gptwp-finance-table-area gptwp-section-box">
                    <h4 class="gptwp-box-title">Movimientos Recientes</h4>
                    <?php echo do_shortcode('[finanzas_tabla_cartera]'); ?>
                </div>
            </div>
            <?php
            $content = ob_get_clean();
            break;

        case 'tab-email':
            $content = do_shortcode('[admin_gestor_correos]');
            break;

        case 'tab-logros':
            $content = do_shortcode('[admin_crear_logro]');
            break;

        case 'tab-cursos':
            $content = do_shortcode('[admin_tabla_cursos]');
            break;

        case 'tab-configuracion':
            $content = gptwp_render_config_form();
            break;

            
        default:
            wp_send_json_error('Tab desconocido');
    }

    wp_send_json_success($content);
});

// ==============================================================================
// === AJAX HANDLER: DETALLES DEL CURSO (MODAL) ===
// ==============================================================================
add_action('wp_ajax_gptwp_get_course_details', function() {
    if (!current_user_can('manage_options') && !current_user_can('shop_manager')) {
        wp_send_json_error('Permisos insuficientes');
    }

    $course_id = isset($_POST['course_id']) ? intval($_POST['course_id']) : 0;
    if (!$course_id) {
        wp_send_json_error('ID de curso inválido');
    }

    $page = isset($_POST['page']) ? intval($_POST['page']) : 1;
    $search = isset($_POST['search']) ? sanitize_text_field($_POST['search']) : '';
    $per_page = 8; // Estudiantes por página

    // Argumentos de búsqueda
    $args = [
        'meta_key' => 'course_'.$course_id.'_access_from',
        'fields'   => 'all_with_meta',
        'number'   => $per_page,
        'paged'    => $page,
    ];

    // Si hay búsqueda
    if (!empty($search)) {
        $args['search'] = '*' . $search . '*';
        $args['search_columns'] = ['user_login', 'user_email', 'display_name'];
    }

    $user_query = new WP_User_Query($args);
    $users = $user_query->get_results();
    $total_users = $user_query->get_total();
    $total_pages = ceil($total_users / $per_page);

    if (empty($users)) {
        wp_send_json_success('<div style="text-align:center; padding:20px; color:#888;">No hay estudiantes inscritos en este curso.</div>');
    }

    // Pre-calcular lecciones del curso para conteo real (sin topics/quizzes Y sin Separadores)
    $course_lessons_raw = learndash_get_course_lessons_list($course_id);
    $course_lessons = [];
    
    // Filtrar Separadores
    if(!empty($course_lessons_raw)) {
        foreach($course_lessons_raw as $l) {
            // Si el título contiene "Separador (Titulo: )", lo ignoramos
            if (strpos($l->post_title, 'Separador (Titulo:') !== false) {
                continue; 
            }
            $course_lessons[] = $l;
        }
    }
    
    $real_total_lessons = count($course_lessons);

    ob_start();
    ?>
    <!-- Buscador AJAX -->
    <div style="margin-bottom: 15px; display:flex; gap:10px;">
        <input type="text" id="gptwp-course-search-input" 
               value="<?php echo esc_attr($search); ?>"
               placeholder="Buscar estudiante por nombre o correo..." 
               style="width:100%; padding:10px; background:#111; border:1px solid #444; color:#fff; border-radius:5px;"
               onkeyup="gptwpDebounceSearch(this.value, <?php echo $course_id; ?>)">
    </div>

    <?php if (empty($users)): ?>
         <div style="text-align:center; padding:20px; color:#888;">No se encontraron estudiantes.</div>
    <?php else: ?>
    <div style="overflow-x:auto;">
        <table id="gptwp-course-students-table" class="gptwp-crm-table" style="width:100%;">
            <thead>
                <tr>
                    <th style="width:60px; text-align:center;">Foto</th>
                    <th>Estudiante</th>
                    <th>Email</th>
                    <th>Progreso</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): 
                    // Progreso General de LD (incluye todo)
                    $progress = learndash_user_get_course_progress($user->ID, $course_id);
                    $percentage = isset($progress['percentage']) ? $progress['percentage'] : 0;
                    
                    // Cálculo manual de Lecciones Completadas real (usando la lista filtrada)
                    $real_completed = 0;
                    if(!empty($course_lessons)) {
                        foreach($course_lessons as $l) {
                             if(learndash_is_lesson_complete($user->ID, $l->ID, $course_id)) {
                                 $real_completed++;
                             }
                        }
                    }
                ?>
                <tr>
                    <td style="text-align:center;">
                        <?php echo get_avatar($user->ID, 40, '', 'Avatar', ['class' => 'gptwp-avatar-img']); ?>
                    </td>
                    <td>
                        <strong style="font-size:14px; color:#fff; display:block; margin-bottom:4px;"><?php echo esc_html($user->display_name); ?></strong>
                        <small style="color:#666; font-family:monospace;">ID: <?php echo $user->ID; ?></small>
                    </td>
                    <td style="font-size:13px; color:#ccc;">
                        <?php echo esc_html($user->user_email); ?>
                    </td>
                    <td>
                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:5px;">
                            <span style="font-weight:800; color:#fff; width:35px;"><?php echo $percentage; ?>%</span>
                            <div class="gptwp-progress-bar-wrapper" style="width:120px; height:6px; background:#333;">
                                <div class="gptwp-progress-bar" style="width: <?php echo $percentage; ?>%; background: <?php echo ($percentage == 100) ? '#4dff88' : 'var(--gold)'; ?>;"></div>
                            </div>
                        </div>
                        <small style="color:#888; font-size:11px; text-transform:uppercase; letter-spacing:0.5px;">
                            <?php echo $real_completed . ' / ' . $real_total_lessons; ?> Lecciones
                        </small>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    
    <!-- Paginador -->
    <?php if ($total_pages > 1): ?>
    <div class="gptwp-pagination" style="display:flex; justify-content:center; align-items:center; gap:10px; margin-top:15px;">
        <?php if ($page > 1): ?>
            <button class="gptwp-btn-action" onclick="gptwpLoadCoursePage(<?php echo $course_id; ?>, <?php echo $page - 1; ?>)">
                 &laquo; Anterior
            </button>
        <?php endif; ?>

        <span style="color:#888; font-size:12px;">Página <?php echo $page; ?> de <?php echo $total_pages; ?></span>

        <?php if ($page < $total_pages): ?>
            <button class="gptwp-btn-action" onclick="gptwpLoadCoursePage(<?php echo $course_id; ?>, <?php echo $page + 1; ?>)">
                 Siguiente &raquo;
            </button>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php endif; // End if empty users ?>


    <?php
    $html = ob_get_clean();
    wp_send_json_success($html);
});



// ==============================================================================
// === MÓDULO 4: DASHBOARD MAESTRO (PANEL CENTRALIZADO) ===
// ==============================================================================
// Uso: [dashboard-master]

add_shortcode('dashboard-master', function() {
    // 1. Seguridad: Solo administradores
    if (!current_user_can('administrator')) {
        return '<div style="padding:20px; color:#F9B137; text-align:center;">⛔ Acceso Restringido: Solo administradores pueden ver este panel.</div>';
    }

    ob_start();
    ?>
    <div class="gptwp-dashboard-master">
        
        <!-- HEADER O NAVBAR -->
        <div class="gptwp-dash-header">
            <h2 class="gptwp-dash-title">Panel de Control</h2>
            <div class="gptwp-dash-nav">
                <button class="gptwp-dash-tab active" data-target="tab-estudiantes">
                    <span class="dashicons dashicons-welcome-learn-more"></span> Estudiantes
                </button>
                <button class="gptwp-dash-tab" data-target="tab-cursos">
                    <span class="dashicons dashicons-book"></span> Cursos
                </button>
                <button class="gptwp-dash-tab" data-target="tab-finanzas">

                    <span class="dashicons dashicons-chart-line"></span> Finanzas
                </button>
                <button class="gptwp-dash-tab" data-target="tab-email">
                    <span class="dashicons dashicons-email"></span> Email Marketing
                </button>
                <button class="gptwp-dash-tab" data-target="tab-logros">
                    <span class="dashicons dashicons-awards"></span> Logros
                </button>
                <button class="gptwp-dash-tab" data-target="tab-configuracion">
                    <span class="dashicons dashicons-admin-generic"></span> Configuración
                </button>
            </div>
        </div>

        <!-- CONTENIDO DE LOS TABS -->
        <div class="gptwp-dash-content">
            
            <!-- TAB 1: ESTUDIANTES -->
            <div id="tab-estudiantes" class="gptwp-tab-pane active">
                
                <!-- KPIs Estudiantes -->
                 <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px;">
                    <h3 style="margin:0; color:#fff;">Resumen</h3>
                    <button class="gptwp-btn-action" onclick="document.getElementById('gptwp-fin-modal').style.display='flex'">
                        <span class="dashicons dashicons-plus-alt2"></span> Nuevo / Importar
                    </button>
                 </div>
                 <div class="gptwp-kpi-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 20px;">
                    <div class="gptwp-kpi-card">
                        <small>Total Alumnos</small>
                        <div class="gptwp-kpi-value"><?php echo do_shortcode('[kpi_total_alumnos]'); ?></div>
                    </div>
                    <div class="gptwp-kpi-card">
                        <small>Nuevos este mes</small>
                        <div class="gptwp-kpi-value text-gold"><?php echo do_shortcode('[kpi_nuevos_alumnos]'); ?></div>
                    </div>
                </div>

                <!-- Gestor de Permisos -->
                <div class="gptwp-section-box">
                    <h4 class="gptwp-box-title">Gestión de Accesos y Permisos</h4>
                    <?php echo do_shortcode('[admin_gestor_permisos]'); ?>
                </div>

            </div>

            <!-- TAB 1.5: CURSOS -->
            <div id="tab-cursos" class="gptwp-tab-pane" data-loaded="false">
                 <div class="gptwp-loader-container" style="text-align:center; padding:50px; color:#666;">
                    <span class="dashicons dashicons-update" style="animation: spin 1s infinite linear; font-size:40px; width:40px; height:40px;"></span>
                    <p style="margin-top:10px;">Cargando Cursos...</p>
                </div>
            </div>


            <!-- TAB 2: FINANZAS (LAYOUT COMPUESTO) -->
            <div id="tab-finanzas" class="gptwp-tab-pane" data-loaded="false">
                <div class="gptwp-loader-container" style="text-align:center; padding:50px; color:#666;">
                    <span class="dashicons dashicons-update" style="animation: spin 1s infinite linear; font-size:40px; width:40px; height:40px;"></span>
                    <p style="margin-top:10px;">Cargando Finanzas...</p>
                </div>
            </div>

            <!-- TAB 3: EMAIL MARKETING -->
            <div id="tab-email" class="gptwp-tab-pane">
                <?php echo do_shortcode('[admin_gestor_correos]'); ?>
            </div>

            <!-- TAB 4: LOGROS -->
            <div id="tab-logros" class="gptwp-tab-pane">
                <?php echo do_shortcode('[admin_crear_logro]'); ?>
            </div>

            <!-- TAB 5: CONFIGURACIÓN -->
            <div id="tab-configuracion" class="gptwp-tab-pane" data-loaded="false">
                <div class="gptwp-loader-container" style="text-align:center; padding:50px; color:#666;">
                    <span class="dashicons dashicons-update" style="animation: spin 1s infinite linear; font-size:40px; width:40px; height:40px;"></span>
                    <p style="margin-top:10px;">Cargando Configuración...</p>
                </div>
            </div>

        </div>
    </div>

    <!-- MODAL FINANZAS -->
    <div id="gptwp-fin-modal" class="gptwp-modal">
        <div class="gptwp-modal-content">
            <span class="gptwp-close" onclick="document.getElementById('gptwp-fin-modal').style.display='none'">&times;</span>
            
            <!-- Modal Tabs -->
            <div class="gptwp-modal-tabs">
                <button class="gptwp-modal-tab active" data-target="m-tab-manual">Registro Manual</button>
                <button class="gptwp-modal-tab" data-target="m-tab-import">Importación Masiva</button>
            </div>

            <!-- Modal Content Panes -->
            <div id="m-tab-manual" class="gptwp-modal-pane active">
                <h3 class="gptwp-modal-title">Nuevo Estudiante</h3>
                <?php echo do_shortcode('[gptwp_registro_estudiante]'); ?>
            </div>
            
            <div id="m-tab-import" class="gptwp-modal-pane">
                <h3 class="gptwp-modal-title">Carga Masiva (CSV/Excel)</h3>
                <?php echo do_shortcode('[gptwp_importador_masivo]'); ?>
            </div>
        </div>
    </div>

    <!-- ESTILOS (SCOPED) -->
    <!-- INTERACTIVIDAD JS (Simple Tab Switcher) -->
    <script>
    // --- 0. Global Course Helpers (Exposed to Window) ---
    // Defined globally so HTML onclick/onkeyup can access them immediately
    var ajaxUrl = "<?php echo admin_url('admin-ajax.php'); ?>";
    window.searchTimeout = null;

    window.gptwpDebounceSearch = function(val, courseId) {
        if(window.searchTimeout) clearTimeout(window.searchTimeout);
        window.searchTimeout = setTimeout(function() {
            gptwpLoadCoursePage(courseId, 1, val);
        }, 500);
    };

    window.gptwpLoadCoursePage = function(courseId, page, searchVal) {
        // Si no se pasa searchVal, tomamos el actual del input
        if (typeof searchVal === 'undefined') {
            var input = document.getElementById('gptwp-course-search-input');
            searchVal = input ? input.value : '';
        }

        var container = document.getElementById('gptwp_course_modal_body');
        if(container) container.style.opacity = '0.5';

        var formData = new FormData();
        formData.append('action', 'gptwp_get_course_details');
        formData.append('course_id', courseId);
        formData.append('page', page);
        formData.append('search', searchVal);

        fetch(ajaxUrl, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if(container) container.style.opacity = '1';
            if(data.success) {
                if(container) container.innerHTML = data.data;
                // Re-enfocar input
                var newInput = document.getElementById('gptwp-course-search-input');
                if(newInput) {
                    newInput.focus();
                    var val = newInput.value;
                    newInput.value = '';
                    newInput.value = val;
                }
            } else {
                alert('Error: ' + (data.data || 'Unknown error'));
            }
        })
        .catch(err => {
            console.error(err);
            if(container) container.style.opacity = '1';
        });
    };

    document.addEventListener('DOMContentLoaded', function() {
        
        // --- 1. Tabs Principales Dashboard ---
        const tabs = document.querySelectorAll('.gptwp-dash-tab');
        const panes = document.querySelectorAll('.gptwp-tab-pane');
        const ajaxUrl = "<?php echo admin_url('admin-ajax.php'); ?>"; // Ensure URL is available

        tabs.forEach(tab => {
            tab.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const targetPane = document.getElementById(targetId);
                
                // Toggle active state
                tabs.forEach(t => t.classList.remove('active'));
                panes.forEach(p => p.classList.remove('active'));
                this.classList.add('active');
                if(targetPane) targetPane.classList.add('active');

                // Lazy Load Logic
                if (targetPane && targetPane.getAttribute('data-loaded') === 'false') {
                    const formData = new FormData();
                    formData.append('action', 'gptwp_load_dashboard_tab');
                    formData.append('tab', targetId);

                    fetch(ajaxUrl, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.success) {
                            targetPane.innerHTML = data.data;
                            targetPane.setAttribute('data-loaded', 'true');
                            
                            // Trigger post-load events
                            setTimeout(() => {
                                window.dispatchEvent(new Event('resize'));
                                
                                // Init Finance Module if this is the finance tab
                                if (targetId === 'tab-finanzas') {
                                    if(typeof window.gptwpInitFinance === 'function') {
                                        window.gptwpInitFinance();
                                    }
                                    if (window.gptwpFinanceChart) {
                                        window.gptwpFinanceChart.resize();
                                    }
                                }
                            }, 50);
                        } else {
                            targetPane.innerHTML = '<p style="color:red; text-align:center;">Error cargando contenido: ' + data.data + '</p>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        targetPane.innerHTML = '<p style="color:red; text-align:center;">Error de conexión.</p>';
                    });
                } else {
                    // Content already loaded, just resize chart if needed
                     if (targetId === 'tab-finanzas') {
                        setTimeout(() => {
                             window.dispatchEvent(new Event('resize'));
                             if (window.gptwpFinanceChart) window.gptwpFinanceChart.resize();
                        }, 50);
                    }
                }
            });
        });

        // --- 2. Delegación Global de Eventos (Modales y Acciones Dinámicas) ---
        document.body.addEventListener('click', function(e) {
            // A. Modal Detalles Curso
            if (e.target.closest('.btn-view-course-details')) {
                const btn = e.target.closest('.btn-view-course-details');
                const courseId = btn.getAttribute('data-course-id');
                const courseName = btn.getAttribute('data-course-name');
                
                // Buscar modal (puede haber sido cargado vía AJAX)
                let modal = document.getElementById('gptwp_course_modal');
                // Si no está en body (por AJAX), moverlo si es necesario o asegurarse que está visible
                if(modal && modal.parentNode !== document.body) {
                    document.body.appendChild(modal); // Mover al root para evitar z-index issues
                }

                if(modal) {
                    document.getElementById('modal_course_name_title').textContent = courseName;
                    modal.style.display = 'flex';
                    
                    const modalBody = document.getElementById('gptwp_course_modal_body');
                    modalBody.innerHTML = '<div style="text-align:center; padding:40px;"><span class="dashicons dashicons-update spin" style="font-size:30px;"></span> Cargando estudiantes...</div>';
                    
                    const formData = new FormData();
                    formData.append('action', 'gptwp_get_course_details');
                    formData.append('course_id', courseId);
                    
                    fetch(ajaxUrl, { method: 'POST', body: formData })
                    .then(res => res.json())
                    .then(data => {
                        if(data.success) { modalBody.innerHTML = data.data; } 
                        else { modalBody.innerHTML = '<p style="color:red; text-align:center;">Error: ' + data.data + '</p>'; }
                    })
                    .catch(err => { console.error(err); modalBody.innerHTML = '<p style="color:red; text-align:center;">Error de conexión.</p>'; });
                }
            }
            // Cerrar cualquier modal al hacer click en close o overlay
            if (e.target.classList.contains('gptwp-modal-overlay') || e.target.classList.contains('gptwp-modal-close')) {
                const modal = e.target.closest('.gptwp-modal-overlay');
                if(modal) modal.style.display = 'none';
            }
        });

        // --- 3. Finance Module Logic (Lazy Loaded) ---
        window.gptwpInitFinance = function() {
            const dateRangeInput = document.getElementById('fin_date_range');
            if(!dateRangeInput || dateRangeInput.classList.contains('initialized')) return;
            
            // Mark as initialized to prevent double init
            dateRangeInput.classList.add('initialized');

            // Inicializar Flatpickr
            const fp = flatpickr("#fin_date_range", {
                mode: "range",
                dateFormat: "Y-m-d",
                defaultDate: [
                    "<?php echo date('Y-m-01'); ?>", 
                    "<?php echo date('Y-m-t'); ?>"
                ],
                locale: "es", 
                theme: "dark",
                onClose: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length === 2) {
                        let start = instance.formatDate(selectedDates[0], "Y-m-d");
                        let end = instance.formatDate(selectedDates[1], "Y-m-d");
                        loadFinanceData(start, end);
                    }
                }
            });

            function loadFinanceData(start, end) {
                if(!start || !end) {
                    let dates = fp.selectedDates;
                    if(dates.length === 2) {
                        start = fp.formatDate(dates[0], "Y-m-d");
                        end = fp.formatDate(dates[1], "Y-m-d");
                    } else {
                        start = "<?php echo date('Y-m-01'); ?>";
                        end = "<?php echo date('Y-m-t'); ?>";
                    }
                }
                
                // Animación de carga
                if(window.jQuery) {
                    jQuery('.gptwp-kpi-value, #fin_cartera_body').css('opacity', 0.5);
                    jQuery.post(ajaxUrl, { 
                        action: 'gptwp_get_finance_data', 
                        start: start, 
                        end: end 
                    }, function(res) {
                        if(res.success) {
                            let d = res.data;
                            jQuery('#val_ingreso_hoy').text(d.kpi.hoy);
                            jQuery('#val_ingreso_rango').text(d.kpi.rango);
                            jQuery('#val_proyeccion').text(d.kpi.proyeccion);
                            jQuery('#val_mora').text(d.kpi.mora);
                            jQuery('.gptwp-kpi-value, #fin_cartera_body').css('opacity', 1);
                            jQuery('#fin_cartera_body').html(d.html_tabla);

                            if(document.getElementById('financeChart')) {
                                renderChart(d.grafica.labels, d.grafica.values);
                            }
                        }
                    });
                }
            }

            function renderChart(labels, dataPoints) {
                const ctx = document.getElementById('financeChart').getContext('2d');
                if (window.gptwpFinanceChart) { window.gptwpFinanceChart.destroy(); }

                let gradient = ctx.createLinearGradient(0, 0, 0, 400);
                gradient.addColorStop(0, 'rgba(249, 177, 55, 0.4)');
                gradient.addColorStop(1, 'rgba(249, 177, 55, 0)');

                window.gptwpFinanceChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Ingresos',
                            data: dataPoints,
                            borderColor: '#F9B137',
                            backgroundColor: gradient,
                            borderWidth: 2,
                            pointBackgroundColor: '#141414',
                            pointBorderColor: '#F9B137',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            fill: true,
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: { 
                                backgroundColor: '#1a1a1a', 
                                titleColor: '#F9B137',
                                bodyColor: '#fff',
                                borderColor: '#333',
                                borderWidth: 1,
                                padding: 10,
                                displayColors: false,
                                callbacks: { label: function(context) { return ' € ' + context.parsed.y; } }
                            }
                        },
                        scales: {
                            x: { grid: { display: false }, ticks: { color: '#666', font: { size: 10 } } },
                            y: { grid: { color: 'rgba(255,255,255,0.05)', borderDash: [4, 4] }, border: { display: false }, ticks: { color: '#666', font: { size: 10 }, callback: function(value) { return '€' + value; } } }
                        }
                    }
                });
            }

            // Carga inicial
            loadFinanceData();
        };

        // --- 3. Tabs del Modal ---
        const modalTabs = document.querySelectorAll('.gptwp-modal-tab');
        const modalPanes = document.querySelectorAll('.gptwp-modal-pane');

        modalTabs.forEach(mtab => {
            mtab.addEventListener('click', function() {
                modalTabs.forEach(t => t.classList.remove('active'));
                modalPanes.forEach(p => p.classList.remove('active'));

                this.classList.add('active');
                const targetId = this.getAttribute('data-target');
                const targetPane = document.getElementById(targetId);
                if(targetPane) targetPane.classList.add('active');
            });
        });

    });
    </script>
    <?php
    return ob_get_clean();
});

// ==============================================================================
// === MÓDULO CURSOS: Visualización y Progreso ===
// ==============================================================================
// Uso: [admin_tabla_cursos]

add_shortcode('admin_tabla_cursos', function() {
    if (!current_user_can('manage_options') && !current_user_can('shop_manager')) {
        return 'Acceso denegado';
    }

    // 1. Obtener todos los cursos publicados
    $args = [
        'post_type'      => 'sfwd-courses',
        'posts_per_page' => -1,
        'post_status'    => 'publish'
    ];
    $courses = get_posts($args);

    ob_start();
    ?>
    <div class="gptwp-card-table">
        <h3 class="gptwp-box-title" style="margin-bottom:20px;">Cursos Activos</h3>
        <div class="gptwp-table-responsive">
            <table class="gptwp-crm-table">
                <thead>
                    <tr>
                        <th>Nombre del Curso</th>
                        <th>Estudiantes</th>
                        <th>Progreso Global</th>
                        <th style="text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($courses)): ?>
                        <tr><td colspan="4" style="text-align:center;">No hay cursos activos.</td></tr>
                    <?php else: foreach ($courses as $course): 
                        $course_id = $course->ID;
                        
                        // 1. Obtener Estudiantes (Método Directo DB para máxima fiabilidad)
                        global $wpdb;
                        $count_query = $wpdb->prepare(
                            "SELECT COUNT(user_id) FROM $wpdb->usermeta WHERE meta_key = %s",
                            'course_' . $course_id . '_access_from'
                        );
                        $student_count = intval($wpdb->get_var($count_query)); // Force boolean/null to 0
                        
                        // Fallback: Si da 0, intentamos con la API de LD
                        if ($student_count === 0 && function_exists('learndash_get_users_for_course')) {
                            $student_count = count(learndash_get_users_for_course($course_id, array(), false));
                        }
                        
                        // 2. Calcular Progreso Global (Cacheado por 1 hora para rendimiento)
                        $transient_key = 'gptwp_course_progress_' . $course_id;
                        $avg_progress = get_transient($transient_key);

                        if ($avg_progress === false) {
                            if ($student_count > 0) {
                                // Para el cálculo del promedio, si necesitamos los IDs, hacemos query ligera
                                $enrolled_user_ids = $wpdb->get_col( $wpdb->prepare(
                                    "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = %s LIMIT 100", // Limit 100 para performance
                                    'course_' . $course_id . '_access_from'
                                ) );

                                $total_percentage = 0;
                                $count_sample = 0;
                                
                                foreach ($enrolled_user_ids as $uid) {
                                    $p = learndash_user_get_course_progress($uid, $course_id);
                                    $pct = isset($p['percentage']) ? $p['percentage'] : 0;
                                    $total_percentage += $pct;
                                    $count_sample++;
                                }
                                $avg_progress = ($count_sample > 0) ? round($total_percentage / $count_sample) : 0;
                            } else {
                                $avg_progress = 0;
                            }
                            set_transient($transient_key, $avg_progress, HOUR_IN_SECONDS);
                        }
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($course->post_title); ?></strong>
                            </td>
                            <td>
                                <span class="gptwp-counter-pill"><?php echo $student_count; ?> Estudiantes</span>
                            </td>
                            <td>
                                <div style="display:flex; align-items:center; gap:10px;">
                                    <span style="font-weight:700; color:#fff; width:35px;"><?php echo $avg_progress; ?>%</span>
                                    <div class="gptwp-progress-bar-wrapper" style="width:100px;">
                                        <div class="gptwp-progress-bar" style="width: <?php echo $avg_progress; ?>%; background: <?php echo ($avg_progress >= 100 ? '#4dff88' : 'var(--gold)'); ?>;"></div>
                                    </div>
                                </div>
                            </td>
                            <td style="text-align:right;">
                                <button class="gptwp-btn-action btn-view-course-details" 
                                        data-course-id="<?php echo $course_id; ?>"
                                        data-course-name="<?php echo esc_attr($course->post_title); ?>">
                                    <span class="dashicons dashicons-visibility"></span> Ver Detalles
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL DETALLES DEL CURSO -->
    <div id="gptwp_course_modal" class="gptwp-modal-overlay">
        <div class="gptwp-modal-content" style="max-width:1200px; width:95%;">
            <div class="gptwp-modal-header">
                <h3 style="margin:0; color:#fff;">Detalle del Curso: <span id="modal_course_name_title">...</span></h3>
                <button class="gptwp-modal-close" onclick="document.getElementById('gptwp_course_modal').style.display='none'">&times;</button>
            </div>
            <div id="gptwp_course_modal_body" class="gptwp-modal-body-scroll">
                <div style="text-align:center; padding:40px;">
                    <span class="dashicons dashicons-update spin" style="font-size:30px;"></span> Cargando estudiantes...
                </div>
            </div>
        </div>
    </div>

    <?php
    return ob_get_clean();
});

// ==============================================================================
// === MÓDULO 5: CONFIGURACIÓN Y PERSONALIZACIÓN DEL DASHBOARD ===
// ==============================================================================

/**
 * Renderiza el formulario de configuración del dashboard
 */
function gptwp_render_config_form() {
    $config = get_option('gptwp_dashboard_config', [
        'primary_color'    => '#f9b137',
        'accent_color'     => '#f9b137',
        'bg_color'         => '#141414',
        'text_color'       => '#ffffff',
        'button_shadow'    => true,
        'card_radius'      => 20,
        'btn_radius'       => 50,
        'btn_border_width' => 0,
        'btn_hover_color'  => '#ffffff',
        'btn_active_color' => '#f9b137'
    ]);
    
    ob_start();
    ?>
    <div class="gptwp-config-form">
        <h3 class="gptwp-box-title">Personalización Visual</h3>
        <p style="color:#888; font-size:13px; margin-bottom:25px;">Define los colores y estilos globales para el dashboard maestro.</p>
        
        <div class="gptwp-config-grid">
            <div class="gptwp-config-item">
                <label>Color Principal (Títulos, Bordes, Iconos)</label>
                <div class="color-picker-wrapper">
                    <input type="color" id="cfg_primary_color" value="<?php echo esc_attr($config['primary_color']); ?>">
                    <span><?php echo esc_html($config['primary_color']); ?></span>
                </div>
            </div>
            
            <div class="gptwp-config-item">
                <label>Color de Acento (Botones, Destacados)</label>
                <div class="color-picker-wrapper">
                    <input type="color" id="cfg_accent_color" value="<?php echo esc_attr($config['accent_color']); ?>">
                    <span><?php echo esc_html($config['accent_color']); ?></span>
                </div>
            </div>
            
            <div class="gptwp-config-item">
                <label>Fondo General de Módulos</label>
                <div class="color-picker-wrapper">
                    <input type="color" id="cfg_bg_color" value="<?php echo esc_attr($config['bg_color']); ?>">
                    <span><?php echo esc_html($config['bg_color']); ?></span>
                </div>
            </div>
            
            <div class="gptwp-config-item">
                <label>Color de Texto Principal</label>
                <div class="color-picker-wrapper">
                    <input type="color" id="cfg_text_color" value="<?php echo esc_attr($config['text_color']); ?>">
                    <span><?php echo esc_html($config['text_color']); ?></span>
                </div>
            </div>
            
            <div class="gptwp-config-item" style="grid-column: span 2; padding: 15px; background: #0a0a0a; border-radius: 8px; margin-top: 10px;">
                <label style="display:flex; align-items:center; gap:12px; cursor:pointer; color:#fff; font-weight:600;">
                    <input type="checkbox" id="cfg_button_shadow" <?php checked($config['button_shadow']); ?> style="width:20px; height:20px; accent-color:#f9b137;">
                    Habilitar Sombreado (Neon/Shadow) en Botones
                </label>
                <p style="color:#666; font-size:11px; margin: 8px 0 0 32px;">Añade un efecto de profundidad y resplandor a los botones principales.</p>
            </div>

            <!-- NUEVAS OPCIONES DE BORDES Y HOVERS -->
            <div class="gptwp-config-item">
                <label>Radio de Bordes: Tarjetas (px)</label>
                <input type="number" id="cfg_card_radius" value="<?php echo (int)$config['card_radius']; ?>" class="gptwp-config-input">
            </div>
            
            <div class="gptwp-config-item">
                <label>Radio de Bordes: Botones (px)</label>
                <input type="number" id="cfg_btn_radius" value="<?php echo (int)$config['btn_radius']; ?>" class="gptwp-config-input">
            </div>

            <div class="gptwp-config-item">
                <label>Grosor del Borde en Botones (px)</label>
                <input type="number" id="cfg_btn_border_width" value="<?php echo (int)$config['btn_border_width']; ?>" class="gptwp-config-input">
            </div>

            <div class="gptwp-config-item">
                <label>Color Hover de Botones</label>
                <div class="color-picker-wrapper">
                    <input type="color" id="cfg_btn_hover_color" value="<?php echo esc_attr($config['btn_hover_color']); ?>">
                    <span><?php echo esc_html($config['btn_hover_color']); ?></span>
                </div>
            </div>

            <div class="gptwp-config-item">
                <label>Color Active de Botones (Click)</label>
                <div class="color-picker-wrapper">
                    <input type="color" id="cfg_btn_active_color" value="<?php echo esc_attr($config['btn_active_color']); ?>">
                    <span><?php echo esc_html($config['btn_active_color']); ?></span>
                </div>
            </div>
        </div>
        
        <!-- Toast de Alerta -->
        <div id="gptwp-config-toast" class="gptwp-toast">
            <span class="gptwp-toast-icon dashicons dashicons-yes"></span>
            <span class="gptwp-toast-message">Configuración actualizada</span>
        </div>
        
        <div style="margin-top:40px; border-top: 1px solid #333; padding-top:20px; display:flex; justify-content: flex-end;">
            <button class="gptwp-btn-save" id="btn_save_config">
                <span class="dashicons dashicons-saved" style="margin-right:8px;"></span> GUARDAR CAMBIOS VISUALES
            </button>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            // Actualizar hex text al cambiar color
            $('.gptwp-config-item input[type="color"]').on('input', function() {
                $(this).next('span').text($(this).val());
            });

            $('#btn_save_config').click(function() {
                const btn = $(this);
                const originalText = btn.html();
                btn.prop('disabled', true).html('<span class="dashicons dashicons-update spin"></span> Guardando...');
                
                const configData = {
                    action: 'gptwp_save_dashboard_config',
                    primary_color: $('#cfg_primary_color').val(),
                    accent_color: $('#cfg_accent_color').val(),
                    bg_color: $('#cfg_bg_color').val(),
                    text_color: $('#cfg_text_color').val(),
                    button_shadow: $('#cfg_button_shadow').is(':checked') ? 'true' : 'false',
                    card_radius: $('#cfg_card_radius').val(),
                    btn_radius: $('#cfg_btn_radius').val(),
                    btn_border_width: $('#cfg_btn_border_width').val(),
                    btn_hover_color: $('#cfg_btn_hover_color').val(),
                    btn_active_color: $('#cfg_btn_active_color').val()
                };
                
                // Usar ajaxUrl global definido arriba o fallback a wp-admin
                const targetUrl = typeof ajaxUrl !== 'undefined' ? ajaxUrl : (typeof ajaxurl !== 'undefined' ? ajaxurl : '/wp-admin/admin-ajax.php');
                
                $.post(targetUrl, configData, function(res) {
                    console.log('Respuesta de guardado:', res);
                    if(res.success) {
                        // Mostrar Alerta Visual
                        const toast = $('#gptwp-config-toast');
                        toast.addClass('show success');
                        setTimeout(() => {
                            toast.removeClass('show success');
                            location.reload();
                        }, 2000);
                    } else {
                        console.error('Error al guardar configuración:', res.data);
                        alert('Error al guardar: ' + res.data);
                        btn.prop('disabled', false).html(originalText);
                    }
                }).fail(function(xhr, status, error) {
                    console.error('Fallo crítico en AJAX:', status, error);
                    alert('Error crítico de conexión al servidor.');
                    btn.prop('disabled', false).html(originalText);
                });
            });
        });
        </script>
        
        <style>
        .gptwp-config-form { background: #141414; padding: 40px; border-radius: 20px; border: 1px solid #333; position: relative; }
        .gptwp-config-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 25px; }
        .gptwp-config-item label { display: block; margin-bottom: 12px; color: #aaa; font-size: 13px; font-weight: bold; }
        .gptwp-config-input { width: 100%; background: #0a0a0a; border: 1px solid #222; border-radius: 8px; color: #fff; padding: 10px; box-sizing: border-box; }
        .color-picker-wrapper { display: flex; align-items: center; gap: 15px; background: #0a0a0a; padding: 8px 15px; border-radius: 8px; border: 1px solid #222; }
        .color-picker-wrapper input[type="color"] { width: 40px; height: 30px; border: none; background: transparent; cursor: pointer; padding: 0; }
        .color-picker-wrapper span { font-family: monospace; color: #888; font-size: 13px; text-transform: uppercase; }
        
        /* Toast Styles */
        .gptwp-toast { position: fixed; bottom: 30px; right: 30px; background: #1a1a1a; color: #fff; padding: 15px 25px; border-radius: 12px; border: 1px solid #333; display: flex; align-items: center; gap: 10px; transform: translateY(100px); transition: 0.3s; z-index: 10001; font-weight: bold; visibility: hidden; opacity: 0; }
        .gptwp-toast.show { visibility: visible; opacity: 1; transform: translateY(0); }
        .gptwp-toast.success { border-color: #4dff88; color: #4dff88; }
        </style>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Handler AJAX para guardar la configuración
 */
add_action('wp_ajax_gptwp_save_dashboard_config', function() {
    if (!current_user_can('manage_options')) wp_send_json_error('Acceso denegado: Se requiere capacidad manage_options');
    
    $config = [
        'primary_color'    => sanitize_hex_color($_POST['primary_color']),
        'accent_color'     => sanitize_hex_color($_POST['accent_color']),
        'bg_color'         => sanitize_hex_color($_POST['bg_color']),
        'text_color'       => sanitize_hex_color($_POST['text_color']),
        'button_shadow'    => $_POST['button_shadow'] === 'true',
        'card_radius'      => (int)$_POST['card_radius'],
        'btn_radius'       => (int)$_POST['btn_radius'],
        'btn_border_width' => (int)$_POST['btn_border_width'],
        'btn_hover_color'  => sanitize_hex_color($_POST['btn_hover_color']),
        'btn_active_color' => sanitize_hex_color($_POST['btn_active_color'])
    ];
    
    update_option('gptwp_dashboard_config', $config);
    wp_send_json_success('Configuración guardada correctamente');
});

/**
 * Inyecta las variables CSS dinámicas basadas en la configuración
 */
add_action('wp_head', function() {
    $config = get_option('gptwp_dashboard_config');
    if (!$config) return;
    
    $primary = $config['primary_color'] ?: '#f9b137';
    $accent = $config['accent_color'] ?: '#f9b137';
    $bg = $config['bg_color'] ?: '#141414';
    $text = $config['text_color'] ?: '#ffffff';
    $card_rad = isset($config['card_radius']) ? (int)$config['card_radius'] : 20;
    $btn_rad = isset($config['btn_radius']) ? (int)$config['btn_radius'] : 50;
    $btn_border = isset($config['btn_border_width']) ? (int)$config['btn_border_width'] : 0;
    $btn_hover = $config['btn_hover_color'] ?: '#ffffff';
    $btn_active = $config['btn_active_color'] ?: '#f9b137';
    
    $shadow_val = $config['button_shadow'] ? '0 5px 20px rgba('.implode(',', gptwp_hex2rgb($accent)).', 0.4)' : 'none';
    $hover_shadow = $config['button_shadow'] ? '0 8px 25px rgba('.implode(',', gptwp_hex2rgb($accent)).', 0.6)' : 'none';
    
    echo "<style id='gptwp-dynamic-styles'>
    :root {
        --accent-gold: $primary !important;
        --card-bg: $bg !important;
        --text-main: $text !important;
        --radius-lg: {$card_rad}px !important;
        --radius: {$card_rad}px !important;
    }
    .gptwp-dashboard-master { 
        --gold: $primary !important; 
        background-color: var(--bg-dark) !important; 
    }
    .gptwp-section-box, .gptwp-card-table, .gptwp-config-form, .gptwp-fluid-form, .gptwp-card { 
        background-color: $bg !important; 
        color: $text !important;
        border-radius: {$card_rad}px !important;
    }
    /* Estilos de Botones */
    .gptwp-btn-save, .gptwp-btn-submit, .btn-enroll, .gptwp-btn-primary { 
        background-color: $accent !important; 
        box-shadow: $shadow_val !important;
        border-radius: {$btn_rad}px !important;
        border: {$btn_border}px solid $primary !important;
        transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
    }
    .gptwp-btn-save:hover, .gptwp-btn-submit:hover, .btn-enroll:hover, .gptwp-btn-primary:hover {
        background-color: $btn_hover !important;
        transform: translateY(-3px) !important;
        box-shadow: $hover_shadow !important;
    }
    .gptwp-btn-save:active, .gptwp-btn-submit:active, .btn-enroll:active, .gptwp-btn-primary:active {
        background-color: $btn_active !important;
        transform: translateY(-1px) !important;
    }

    .gptwp-box-title, .gptwp-dash-title, .gptwp-form-head { 
        color: $primary !important; 
        border-color: $primary !important;
    }
    .gptwp-dash-tab.active {
        color: $primary !important;
        border-bottom-color: $primary !important;
    }
    .gptwp-kpi-value { 
        color: $primary !important; 
    }
    </style>";
}, 100);

/**
 * Helper para convertir HEX a RGB (para opacidad en sombras)
 */
function gptwp_hex2rgb($hex) {
    if (!$hex) return [0,0,0];
    $hex = str_replace('#', '', $hex);
    if(strlen($hex) == 3) {
        $r = hexdec(substr($hex,0,1).substr($hex,0,1));
        $g = hexdec(substr($hex,1,1).substr($hex,1,1));
        $b = hexdec(substr($hex,2,1).substr($hex,2,1));
    } else {
        $r = hexdec(substr($hex,0,2));
        $g = hexdec(substr($hex,2,2));
        $b = hexdec(substr($hex,4,2));
    }
    return [$r, $g, $b];
}


