<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-pdf
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypePdf extends PluginBlocktype {

    public static function single_only() {
        return false;
    }

    public static function get_title() {
        return get_string('title', 'blocktype.file/pdf');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.file/pdf');
    }

    public static function get_categories() {
        return array('fileimagevideo');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        require_once(get_config('docroot') . 'lib/view.php');
        $configdata = $instance->get('configdata'); // this will make sure to unserialize it for us
        $configdata['viewid'] = $instance->get('view');
        $view = new View($configdata['viewid']);
        $group = $view->get('group');

        if (isset($configdata['artefactid'])) {
            $pdf = $instance->get_artefact_instance($configdata['artefactid']);

            if (!file_exists($pdf->get_path())) {
                return '';
            }

            $urlbase = get_config('wwwroot');
            // edit view doesn't use subdomains, neither do groups
            if (get_config('cleanurls') && get_config('cleanurlusersubdomains') && !$editing && empty($group)) {
                global $USER;
                $userurlid = $USER->get('urlid');
                if ($urlallowed = !is_null($userurlid) && strlen($userurlid)) {
                    $urlbase = profile_url($USER) . '/';
                }
            }

            return '<iframe src="' . $urlbase . 'artefact/file/blocktype/pdf/viewer.php?editing=' . $editing . '&ingroup=' . !empty($group) . '&file=' . $configdata['artefactid'] . '&view=' . $instance->get('view')
                 . '" width="100%" height="500" frameborder="0"></iframe>';
        }

        return '';
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');
        safe_require('artefact', 'file');
        $instance->set('artefactplugin', 'file');
        return array(
            'artefactid' => self::filebrowser_element($instance, (isset($configdata['artefactid'])) ? array($configdata['artefactid']) : null),
        );
    }

    private static function get_allowed_mimetypes() {
        static $mimetypes = array();
        if (!$mimetypes) {
            $mimetypes = get_column('artefact_file_mime_types', 'mimetype', 'description', 'pdf');
        }
        return $mimetypes;
    }

    public static function filebrowser_element(&$instance, $default=array()) {
        $element = ArtefactTypeFileBase::blockconfig_filebrowser_element($instance, $default);
        $element['title'] = get_string('file', 'artefact.file');
        $element['name'] = 'artefactid';
        $element['config']['selectone'] = true;
        $element['filters'] = array(
            'artefacttype'    => array('file'),
            'filetype'        => self::get_allowed_mimetypes(),
        );
        return $element;
    }

    public static function artefactchooser_element($default=null) {
        return array(
            'name'  => 'artefactid',
            'type'  => 'artefactchooser',
            'title' => get_string('file', 'artefact.file'),
            'defaultvalue' => $default,
            'blocktype' => 'html',
            'limit' => 10,
            'artefacttypes' => array('file'),
            'template' => 'artefact:file:artefactchooser-element.tpl',
        );
    }

    public static function default_copy_type() {
        return 'full';
    }

}
