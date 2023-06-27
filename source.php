<?php

//  Version 2.1 2023-06-27 Forked by KeanuTang
//  An email template for sending an email to the site admin when an UM User Profile is updated.
//  Forked version includes before/after changes in the email.
//  Source: https://github.com/KeanuTang/UM-Admin-User-Profile-Update-Email.git

add_action( 'um_user_before_updating_profile', 'custom_before_profile_is_updated', 10, 3);
add_filter( 'um_email_notifications', 'custom_email_notifications_profile_is_updated', 10, 1 );
add_action( 'um_user_after_updating_profile', 'custom_profile_is_updated_email', 10, 3 );
add_action( 'profile_update', 'custom_profile_is_updated_email_backend', 10, 3 );
add_filter( 'um_admin_settings_email_section_fields', 'um_admin_settings_email_section_fields_custom_forms', 10, 2 );

// Save off the user profile prior to processing the update
function custom_before_profile_is_updated($userinfo) {
    UM()->user()->prior_data = $userinfo;
}

function custom_email_notifications_profile_is_updated( $emails ) {

    $custom_emails = array(
                'profile_is_updated_email' => array(
                'key'			 => 'profile_is_updated_email',
                'title'			 => __( 'Profile is updated email', 'ultimate-member' ),
                'subject'		 => 'Profile Update {username}',
                'body'			 => '',
                'description'	 => __( 'To send an email to the site admin when a user profile is updated', 'ultimate-member' ),
                'recipient'		 => 'admin',
                'default_active' => true ));

    UM()->options()->options = array_merge( array(  'profile_is_updated_email_on'  => 1,
                                                    'profile_is_updated_email_sub' => 'Profile Update {username}', ), 
                                            UM()->options()->options );

    return array_merge( $custom_emails, $emails );
}

function um_admin_settings_email_section_fields_custom_forms( $section_fields, $email_key ) {

    if( $email_key == 'profile_is_updated_email' ) {
        $section_fields[] = array(
                'id'            => $email_key . '_custom_forms',
                'type'          => 'text',
                'label'         => __( 'Include these UM Profile Forms for sending emails:', 'ultimate-member' ),
                'conditional'   => array( $email_key . '_on', '=', 1 ),
                'tooltip'       => __( 'Comma separated UM Profile Form IDs, empty send emails always.', 'ultimate-member' )
                );
    }
    return $section_fields;
}

function custom_profile_is_updated_email_backend( $user_id, $old_data, $user_data ) {

    if( isset( $_REQUEST['action']) && $_REQUEST['action'] == 'update' ) {
        custom_profile_is_updated_email( $user_data, $user_id );
    }
}

function custom_profile_is_updated_email( $to_update, $user_id, $args = array() ) {

    global $current_user;

    $old_data = UM()->user()->prior_data;  //User profile prior to processing updates
    $user_fields = array();  //Field display names
    foreach ( UM()->builtin()->all_user_fields() as $key => $arr ) {
        $user_fields[ $key ] = isset( $arr['title'] ) ? $arr['title'] : '';
    }

    $forms = UM()->options()->get( 'profile_is_updated_email_custom_forms' );

    if( !empty( $forms )) {
        $forms = explode( ',', $forms ); 
        if( is_array( $forms ) && !in_array( $args['form_id'], $forms )) return;
    }

    $submitted = um_user( 'submitted' );    
    foreach( $to_update as $key => $value ) {
        $submitted[$key] = $value;
    }

    $registration_form_id = $submitted['form_id'];
    $registration_timestamp = um_user( 'timestamp' );
    $submitted['form_id'] = $args['form_id'];
    
    update_user_meta( $user_id, 'submitted', $submitted );
    update_user_meta( $user_id, 'timestamp', current_time( 'timestamp' ) );
    UM()->user()->remove_cache( $user_id );
    um_fetch_user( $user_id );

    $time_format = get_option( 'date_format' ) . ' ' . get_option( 'time_format' );
    um_fetch_user( $user_id );

    //Diff the old and new values
    $fields_updated;
    foreach( $to_update as $key => $value ) {
        if ($value != $old_data[$key]) {
            $before = strlen($old_data[$key])>0 ? $old_data[$key] : '&lt;blank&gt;';
            $after = strlen($value)>0 ? $value : '&lt;blank&gt;';
            $fields_updated .= '<tr><td style="padding: .5rem">' . $user_fields[$key] . ' ' . $after . '</td>';
            $fields_updated .= '<td style="padding: .5rem">' . $user_fields[$key] . ' ' . $before . '</td></tr>';
        }
    }
    if (strlen($fields_updated)>0){
        $fields_updated = '<table><thead><td style="padding: .5rem"><strong>Updated Information</strong></td><td style="padding: .5rem"><strong>Old Information</strong></td></thead>' . $fields_updated . '</table>';
    }
    
    $args['tags'] = array(  '{profile_url}',
                            '{current_date}',
                            '{updating_user}', 
                            '{fields_updated}' );

    $args['tags_replace'] = array(  um_user_profile_url( $user_id ), 
                                    date_i18n( $time_format, current_time( 'timestamp' )), 
                                    $current_user->user_login, 
                                    $fields_updated);

    UM()->mail()->send( get_bloginfo( 'admin_email' ), 'profile_is_updated_email', $args );

    $submitted['form_id'] = $registration_form_id;

    update_user_meta( $user_id, 'submitted', $submitted );
    update_user_meta( $user_id, 'timestamp', $registration_timestamp );
    UM()->user()->remove_cache( $user_id );
    um_fetch_user( $user_id );
}
