<?php
/**
 * Classe de test parente pour le plugin WS Font Awesome to SVG.
 *
 * Toutes les classes de test étendent celle-ci, JAMAIS WP_Mock\Tools\TestCase
 * directement. Elle gère :
 *   - WP_Mock::setUp() / tearDown()
 *   - Reset des superglobales $_GET / $_SERVER
 *   - Helper invoke_static() pour Reflection sur méthodes privées
 *   - Helper assert_html_contains() pour matcher dans du HTML
 *
 * @package WS_FA_To_SVG
 */

namespace WS_FA_To_SVG\Tests\Unit;

use WP_Mock\Tools\TestCase;
use ReflectionClass;
use ReflectionMethod;

abstract class WebStrategyTestCase extends TestCase {

	/**
	 * setUp WP_Mock.
	 *
	 * @return void
	 */
	public function setUp(): void {
		\WP_Mock::setUp();

		// Reset des superglobales (chaque test repart d'un état propre).
		$_GET    = array();
		$_POST   = array();
		$_SERVER = array(
			'REQUEST_URI'    => '/',
			'REQUEST_METHOD' => 'GET',
			'HTTP_HOST'      => 'example.test',
		);
	}

	/**
	 * tearDown WP_Mock.
	 *
	 * @return void
	 */
	public function tearDown(): void {
		\WP_Mock::tearDown();
		\Mockery::close();
		parent::tearDown();
	}

	/**
	 * Invoque une méthode statique privée/protégée via Reflection.
	 *
	 * @param string $class  Nom de classe.
	 * @param string $method Nom de méthode.
	 * @param array  $args   Arguments.
	 * @return mixed
	 */
	protected function invoke_static( $class, $method, array $args = array() ) {
		$rm = new ReflectionMethod( $class, $method );
		$rm->setAccessible( true );
		return $rm->invokeArgs( null, $args );
	}

	/**
	 * Invoque une méthode privée/protégée d'instance via Reflection.
	 *
	 * @param object $instance Instance.
	 * @param string $method   Nom de méthode.
	 * @param array  $args     Arguments.
	 * @return mixed
	 */
	protected function invoke_method( $instance, $method, array $args = array() ) {
		$rm = new ReflectionMethod( $instance, $method );
		$rm->setAccessible( true );
		return $rm->invokeArgs( $instance, $args );
	}

	/**
	 * Lit une propriété privée/protégée via Reflection.
	 *
	 * @param object $instance Instance.
	 * @param string $name     Nom de propriété.
	 * @return mixed
	 */
	protected function get_property( $instance, $name ) {
		$rc = new ReflectionClass( $instance );
		$rp = $rc->getProperty( $name );
		$rp->setAccessible( true );
		return $rp->getValue( $instance );
	}

	/**
	 * Définit une propriété privée/protégée via Reflection.
	 *
	 * @param object $instance Instance.
	 * @param string $name     Nom de propriété.
	 * @param mixed  $value    Valeur.
	 * @return void
	 */
	protected function set_property( $instance, $name, $value ) {
		$rc = new ReflectionClass( $instance );
		$rp = $rc->getProperty( $name );
		$rp->setAccessible( true );
		$rp->setValue( $instance, $value );
	}

	/**
	 * Assert qu'une chaîne contient une sous-chaîne (helper pour HTML).
	 *
	 * @param string $needle   Chaîne attendue.
	 * @param string $haystack Chaîne complète.
	 * @param string $message  Message d'erreur.
	 * @return void
	 */
	protected function assert_contains_substring( $needle, $haystack, $message = '' ) {
		$this->assertStringContainsString( $needle, $haystack, $message );
	}
}
