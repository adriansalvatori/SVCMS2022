<?php if (!defined('ABSPATH')) {
    exit;
}

final class PH_Upgrade_Project_Access_4_0 extends PH_Upgrade
{
    /**
     * Unique Identifier for upgrade routine
     * @var string
     */
    public $name = 'data-project-access-4-0';

    /**
     * Get 100 projects at a time
     * @var int
     */
    public $offset = 100;

    /**
     * User-Facing identifier for upgrade routine
     * @var string
     */
    public $nice_name = '4.0 Project Access Data Upgrade';

    /**
     * The priority determines the oder in which the upgrades are run.
     * Use a version for this one.
     *
     * @var string
     */
    public $priority = '4.0';

    /**
     * Upgrade user-facing description
     * @var string
     */
    public $description = 'An update is necessary for project access data changes in 4.0. This may take a while if you have a lot of projects. Please don\'t navigate away from this page.';

    /**
     * Store meta keys for upgrade
     *
     * @var array
     */
    public $meta_keys = array();

    /**
     * The loading method is used to setup the upgrade and is called by the Upgrade Handler.
     * @return array
     */
    public function loading()
    {
        $this->args['sites'] = array();

        // handle multisite
        if (is_multisite()) {
            $sites = get_sites(
                array(
                    'number' => 100,
                    'fields' => 'ids',
                )
            );

            if (!empty($sites)) {
                $x = 1;
                foreach ($sites as $site_id) {
                    switch_to_blog($site_id);
                    $projects = $this->get_all_projects();
                    restore_current_blog();
                    $this->args['sites'][$x] = array(
                        'site_id' => $projects,
                    );
                    $x++;
                }
            }

            if (empty($this->total_steps) || $this->total_steps <= 1) {
                $this->total_steps = count($sites);
            }
        } else {
            $projects               = $this->get_all_projects();
            $this->args['projects'] = true;

            if (empty($this->total_steps) || $this->total_steps <= 1) {
                $this->total_steps = (count($projects) / $this->offset) + 1;
            }
        }

        $args = array(
            'total_steps' => $this->total_steps,
            'step'        => $this->getLastStep(),
        );

        return $args;
    }

    public function _beforeStep($step)
    {
    }

    public function step($step)
    {
        if (isset($this->args['sites'])) {
            $site = $this->args['sites'][$step];

            $site_id = key($site);
            $projects = $site[$site_id];

            switch_to_blog($site_id);
            if (!empty($projects)) {
                foreach ($projects as $project_id) {
                    if ($project_id) {
                        $this->upgrade_data($project_id);
                    }
                }
            }
            restore_current_blog();
        } elseif (isset($this->args['threads'])) {
            // get thread chunk based on step offset
            $projects = $this->get_projects_chunk($step);
            foreach ($projects as $project) {
                $this->upgrade_data($project);
            }
        }
    }

    /**
     * Upgrade comment data
     *
     * @param $id
     */
    public function upgrade_data($project_id)
    {
        // use allow_guests if project access is public
        if ('public' === get_post_meta($project_id, 'project_access', true)) {
            update_post_meta($project_id, 'allow_guests', true);
        }

        // use force login if project access is login
        if ('login' === get_post_meta($project_id, 'project_access', true)) {
            update_post_meta($project_id, 'force_login', true);
        }
    }

    public function complete()
    {
        ph_log('4.0 Project Access Update Completed');
        update_site_option('ph_data_upgrade_version', PH_VERSION);
    }

    public function isComplete()
    {
        // don't run for new installs
        if (false === get_site_option('ph_data_upgrade_version', false)) {
            return true;
        }

        // if newer than 2.6.0, it's complete
        if (version_compare(get_site_option('ph_data_upgrade_version'), '3.9.99', '<')) {
            return false;
        }

        return true;
    }

    public function get_all_projects()
    {
        $projects = new WP_Query(
            array(
                'post_type'      => array('ph-project', 'ph-website'),
                'posts_per_page' => -1,
                'fields'         => 'ids',
            )
        );

        return (array) $projects->posts;
    }

    public function get_projects_chunk($step)
    {
        $projects = new WP_Query(
            array(
                'post_type'      => array('ph-project', 'ph-website'),
                'offset'         => ($this->offset * ($step - 1)),
                'posts_per_page' => $this->offset,
            )
        );

        return (array) $projects->posts;
    }
}

function ph_register_upgrade_project_access_4_0($upgrades)
{
    $upgrades[] = new PH_Upgrade_Project_Access_4_0();
    return $upgrades;
}

add_action('ph_upgrade_handler_register', 'ph_register_upgrade_project_access_4_0');
