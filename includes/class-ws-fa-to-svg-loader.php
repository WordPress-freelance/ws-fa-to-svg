<?php
/**
 * Loader WPPB.
 *
 * Enregistre les actions et filtres qui seront déclenchés à `run()`.
 *
 * @package WS_FA_To_SVG
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WS_FA_To_SVG_Loader
 */
class WS_FA_To_SVG_Loader {

	/**
	 * Actions enregistrées.
	 *
	 * @var array
	 */
	protected $actions = array();

	/**
	 * Filtres enregistrés.
	 *
	 * @var array
	 */
	protected $filters = array();

	/**
	 * Shortcodes enregistrés.
	 *
	 * @var array
	 */
	protected $shortcodes = array();

	/**
	 * Ajoute une action.
	 *
	 * @param string $hook          Nom du hook.
	 * @param object $component     Instance du callback.
	 * @param string $callback      Méthode du callback.
	 * @param int    $priority      Priorité (défaut 10).
	 * @param int    $accepted_args Nombre d'arguments (défaut 1).
	 * @return void
	 */
	public function add_action( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Ajoute un filtre.
	 *
	 * @param string $hook          Nom du hook.
	 * @param object $component     Instance du callback.
	 * @param string $callback      Méthode du callback.
	 * @param int    $priority      Priorité (défaut 10).
	 * @param int    $accepted_args Nombre d'arguments (défaut 1).
	 * @return void
	 */
	public function add_filter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) {
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
	}

	/**
	 * Ajoute un shortcode.
	 *
	 * @param string $tag       Tag du shortcode.
	 * @param object $component Instance du callback.
	 * @param string $callback  Méthode du callback.
	 * @return void
	 */
	public function add_shortcode( $tag, $component, $callback ) {
		$this->shortcodes[] = array(
			'tag'       => $tag,
			'component' => $component,
			'callback'  => $callback,
		);
	}

	/**
	 * Ajoute un hook au tableau approprié.
	 *
	 * @param array  $hooks         Tableau cible.
	 * @param string $hook          Nom du hook.
	 * @param object $component     Composant.
	 * @param string $callback      Callback.
	 * @param int    $priority      Priorité.
	 * @param int    $accepted_args Args.
	 * @return array
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args ) {
		$hooks[] = array(
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args,
		);
		return $hooks;
	}

	/**
	 * Enregistre tous les hooks auprès de WordPress.
	 *
	 * @return void
	 */
	public function run() {
		foreach ( $this->filters as $hook ) {
			add_filter( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->actions as $hook ) {
			add_action( $hook['hook'], array( $hook['component'], $hook['callback'] ), $hook['priority'], $hook['accepted_args'] );
		}

		foreach ( $this->shortcodes as $sc ) {
			add_shortcode( $sc['tag'], array( $sc['component'], $sc['callback'] ) );
		}
	}
}
