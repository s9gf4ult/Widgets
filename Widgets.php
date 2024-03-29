<?php
/**
 *
 * {{#widget:<WidgetName>|<name1>=<value1>|<name2>=<value2>}}
 *
 * @author Sergey Chernyshev
 * @version $Id: Widgets.php 15 2008-06-25 21:22:40Z sergey.chernyshev $
 */

if ( !defined( 'MEDIAWIKI' ) ) {
    echo "This file is not a valid entry point.";
    exit( 1 );
}

$wgExtensionCredits['parserhook'][] = array(
	'path' => __FILE__,
	'name' => 'Widgets',
	'descriptionmsg' => 'widgets-desc',
	'version' => '0.10.0',
	'author' => '[http://www.sergeychernyshev.com Sergey Chernyshev]',
	'url' => 'https://www.mediawiki.org/wiki/Extension:Widgets'
);

/**
 * Set this to the index of the Widget namespace
 */
if ( !defined( 'NS_WIDGET' ) ) {
   define( 'NS_WIDGET', 274 );
}
if ( !defined( 'NS_WIDGET_TALK' ) ) {
   define( 'NS_WIDGET_TALK', NS_WIDGET + 1 );
} elseif ( NS_WIDGET_TALK != NS_WIDGET + 1 ) {
   throw new MWException( 'Configuration error. Do not define NS_WIDGET_TALK, it is automatically set based on NS_WIDGET.' );
}

// Support subpages only for talk pages by default
$wgNamespacesWithSubpages[NS_WIDGET_TALK] = true;

// Define new right
$wgAvailableRights[] = 'editwidgets';

// Assign editing to widgeteditor and sysop groups only (widgets can be dangerous so we do it here, not in LocalSettings)
$wgGroupPermissions['*']['editwidgets'] = false;
$wgGroupPermissions['widgeteditor']['editwidgets'] = true;
$wgGroupPermissions['sysop']['editwidgets'] = true;

// Set this to true to use FlaggedRevs extension's stable version for widget security
$wgWidgetsUseFlaggedRevs = false;

$dir = dirname( __FILE__ ) . '/';

// Initialize Smarty
require_once( $dir . 'smarty/libs/Smarty.class.php' );
$wgExtensionMessagesFiles['Widgets'] = $dir . 'Widgets.i18n.php';
$wgExtensionMessagesFiles['WidgetsNamespaces'] = $dir . 'Widgets.i18n.namespaces.php';
$wgAutoloadClasses['WidgetRenderer'] = $dir . 'WidgetRenderer.php';

$wgExtensionMessagesFiles['WidgetsMagic'] = $dir . 'Widgets.i18n.magic.php';

// Parser function registration
$wgExtensionFunctions[] = 'widgetNamespacesInit';
$wgHooks['ParserFirstCallInit'][] = 'widgetParserFunctions';
$wgHooks['ParserAfterTidy'][] = 'processEncodedWidgetOutput';

/**
 * @param $parser Parser
 * @return bool
 */
function widgetParserFunctions( &$parser ) {
	$parser->setFunctionHook( 'widget', array( 'WidgetRenderer', 'renderWidget' ) );

	return true;
}

function processEncodedWidgetOutput( &$out, &$text ) {
	// Find all hidden content and restore to normal
	$text = preg_replace(
		'/ENCODED_CONTENT ([0-9a-zA-Z\/+]+=*)* END_ENCODED_CONTENT/esm',
		'base64_decode("$1")',
		$text
	);

	return true;
}

function widgetNamespacesInit() {
	global $wgNamespaceProtection, $wgWidgetsUseFlaggedRevs;

	if ( !$wgWidgetsUseFlaggedRevs ) {
		// Setting required namespace permission rights
		$wgNamespaceProtection[NS_WIDGET] = array( 'editwidgets' );
	}
}
