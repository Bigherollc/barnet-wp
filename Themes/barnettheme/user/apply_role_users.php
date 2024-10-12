<?php
$users = get_users();
$userOptions = array_map(function ($e) {
    return '<option value="' . $e->ID . '">' . $e->user_login . '</option>';
}, $users);

$roles = wp_roles()->roles;
array_walk($roles, function (&$a, $b) {
    $a = '<option value="' . $b . '">' . $a["name"] . '</option>';
});

if (isset($_POST['submit'])) {
    ?>
    <div id="aru_message" class="updated notice notice-success is-dismissible">
        <p>Roles Updated.</p>
        <button type="button" class="notice-dismiss" id="aru_notice_dismiss">
            <span class="screen-reader-text">Dismiss this notice.</span>
        </button>
    </div>
    <?php
}
?>

<div>
    <form method="post">
        <?php wp_nonce_field('apply_role_users'); ?>
        <h1>Apply Role Users</h1>
        <table class="form-table">
            <tr valign="top">
                <td>
                    <select name="sel_user[]" multiple="multiple">
                        <?php echo implode($userOptions); ?>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <td>
                    <select name="sel_role[]" multiple="multiple">
                        <?php echo implode($roles); ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <?php submit_button(); ?>
                </td>
            </tr>
        </table>
    </form>
</div>
