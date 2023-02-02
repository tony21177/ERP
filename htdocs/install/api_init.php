<?php
define('DONOTLOADCONF', 1); // To avoid loading conf by file inc.php
include('../conf/init.php');
error_log('[api_init.php] before include inc.php');
error_log('[api_init.php] ');
$dolibarr_main_data_root = $root_folder;
define('DOL_DOCUMENT_ROOT', $dolibarr_main_data_root);
$dolibarr_main_document_root_alt = $dolibarr_main_data_root . '/custom';
include 'inc.php';
global $conf;
error_log('[api_init.php] after include inc.php $conf->file->dol_document_root:' . print_r($conf->file->dol_document_root, true));
error_log('[api_init.php] after include inc.php');
error_log('....DOL_DATA_ROOT=' . DOL_DATA_ROOT);
global $langs;

$comp = '';
$login = '';
$pass = '';
$inputJSON = file_get_contents('php://input');
error_log('[api_init.php]post raw body:' . $inputJSON);
if ($inputJSON && !empty($inputJSON)) {
	$post_body = json_decode($inputJSON, TRUE);
	error_log('[api_init.php]post raw body:' . $post_body);
	if (!empty($post_body) && is_null($post_body)) {
		return json_encode(
			array(
				"IsSuccess" => false,
				"message" => "parameter error",
			),
			JSON_UNESCAPED_UNICODE
		);

	}
	if (!empty($post_body) && !array_key_exists('login', $post_body)) {
		$postJsonHasComp = true;
		return json_encode(
			array(
				"IsSuccess" => false,
				"message" => "parameter login is required",
			),
			JSON_UNESCAPED_UNICODE
		);

	}
	if (!empty($post_body) && !array_key_exists('pass', $post_body)) {
		$postJsonHasComp = true;
		return json_encode(
			array(
				"IsSuccess" => false,
				"message" => "parameter pass is required",
			),
			JSON_UNESCAPED_UNICODE
		);

	}
	if (!empty($post_body) && !array_key_exists('comp', $post_body)) {
		$postJsonHasComp = true;
		return json_encode(
			array(
				"IsSuccess" => false,
				"message" => "parameter comp is required",
			),
			JSON_UNESCAPED_UNICODE
		);

	}
	if (!empty(!empty($post_body))) {
		$login = $post_body['login'];
		$pass = $post_body['pass'];
		$comp = $post_body['comp'];
	}

}
if (array_key_exists('login', $_POST)) {
	$login = $_POST['login'];
}
if (array_key_exists('pass', $_POST)) {
	$pass = $_POST['pass'];
}
if (array_key_exists('comp', $_POST)) {
	$comp = $_POST['comp'];
}

error_log('[api_ini.php]login=' . $login . ',pass=' . $pass . ',comp=' . $comp);

// 設定變數
$action = 'set';
$setuplang = 'zh_TW';
$langs->setDefaultLang($setuplang);
$langs->loadLangs(array("admin", "install", "errors"));
// Dolibarr pages directory
include_once '../conf/init.php';
global $root_folder;
$main_dir = $root_folder;
// Directory for generated documents (invoices, orders, ecm, etc...)
$main_data_dir = $root_folder . '/documents';
// Dolibarr root URL
$main_url = 'http://localhost';
// Database login information
$userroot = 'root';
$passroot = 'root';
// Database server
$db_type = 'mysqli';
$db_host = 'localhost';
$db_name = $comp;
$db_user = 'root';
$db_pass = 'root';
$db_port = 3306;
$db_prefix = 'llx_';
$main_db_prefix = (!empty($db_prefix) ? $db_prefix : 'llx_');
$db_create_database = 'on';
$db_create_user = '';
// Force https
$main_force_https = ('0');
// Use alternative directory
$main_use_alt_dir = ('');
// Alternative root directory name
$main_alt_dir_name = ('custom');

$dolibarr_main_distrib = 'standard';
error_log('[api_init.php] before execute step1 $conf->file->dol_document_root:' . print_r($conf->file->dol_document_root, true));
$error_msg = step1($comp);
error_log('executed step1 return:' . $error_msg);

if ($error_msg) {
	echo $error_msg;
	exit;
}
error_log('[api_init.php] before execute step2 $conf->file->dol_document_root:' . print_r($conf->file->dol_document_root, true));
$error_msg = step2($comp);
error_log('executed step2 return:' . $error_msg);

if ($error_msg) {
	echo $error_msg;
	exit;
}

error_log('[api_init.php] before execute step5 $conf->file->dol_document_root:' . print_r($conf->file->dol_document_root, true));
$error_msg = step5($comp, $login, $pass);
error_log('executed step5 return:' . $error_msg);

if ($error_msg) {
	echo $error_msg;
	exit;
}

echo json_encode(
	array(
		"IsSuccess" => true,
		"message" => '',
	),
	JSON_UNESCAPED_UNICODE
);
exit;




function step1($comp)
{
	error_log('[api_ini.php]begin step1...');
	global $conf, $langs;
	$error_msg = '';
	global $action;
	global $setuplang;
	global $langs;
	// 新增不同公司將設定寫到不同的${comp}_conf.php
	if (!empty($comp)) {
		$conffile = '../conf/' . $comp . '_conf.php';
		$conffiletoshow = 'htdocs/conf/' . $comp . '_conf.php';
	}
	if (!file_exists("$conffile")) {
		$file = fopen($conffile, "w");
		fwrite($file, '');
	}

	// Dolibarr pages directory
	global $root_folder, $root_folder, $main_data_dir, $main_url, $userroot, $passroot, $db_type, $db_host, $db_name, $db_user, $db_pass, $db_port, $db_create_database, $db_create_user, $main_force_https, $main_use_alt_dir, $main_alt_dir_name, $dolibarr_main_distrib, $main_dir;

	session_start(); // To be able to keep info into session (used for not losing password during navigation. The password must not transit through parameters)

	// Save a flag to tell to restore input value if we go back
	$_SESSION['dol_save_pass'] = $db_pass;
	//$_SESSION['dol_save_passroot']=$passroot;

	// Now we load forced values from install.forced.php file.
	$useforcedwizard = false;
	$forcedfile = "./install.forced.php";
	if ($conffile == "/etc/dolibarr/conf.php") {
		$forcedfile = "/etc/dolibarr/install.forced.php";
	}
	if (@file_exists($forcedfile)) {
		$useforcedwizard = true;
		include_once $forcedfile;
		// If forced install is enabled, replace the post values. These are empty because form fields are disabled.
		if ($force_install_noedit) {
			$main_dir = detect_dolibarr_main_document_root();
			$main_url = detect_dolibarr_main_url_root();
		}
		if (!empty($force_install_distrib)) {
			$dolibarr_main_distrib = $force_install_distrib;
		}
	}
	$error = 0;
	/*
	 *	View
	 */

	error_log("--- step1: entering step1.php page");

	// pHeader($langs->trans("ConfigurationFile"), "step2");

	// Test if we can run a first install process
	if (!is_writable($conffile)) {
		$error_msg = $error_msg . $langs->trans("ConfFileIsNotWritable", $conffiletoshow);
		return json_encode(
			array(
				"IsSuccess" => false,
				"message" => $error_msg,
			),
			JSON_UNESCAPED_UNICODE
		);
	}
	// Check parameters
	$is_sqlite = false;
	$is_sqlite = ($db_type === 'sqlite' || $db_type === 'sqlite3');
	// Remove last / into dans main_dir
	if (substr($main_dir, dol_strlen($main_dir) - 1) == "/") {
		$main_dir = substr($main_dir, 0, dol_strlen($main_dir) - 1);
	}
	// Remove last / into dans main_url
	if (!empty($main_url) && substr($main_url, dol_strlen($main_url) - 1) == "/") {
		$main_url = substr($main_url, 0, dol_strlen($main_url) - 1);
	}
	if (!dol_is_dir($main_dir . '/core/db/')) {
		$error_msg = $error_msg . $langs->trans("ErrorBadValueForParameter", $main_dir, $langs->transnoentitiesnoconv("WebPagesDirectory"));
		$error++;
		error_log('ErrorBadValueForParameter' . $error_msg);
		return json_encode(
			array(
				"IsSuccess" => false,
				"message" => $error_msg,
			),
			JSON_UNESCAPED_UNICODE
		);

	}
	// Test database connection
	if (!$error) {
		$result = @include_once $main_dir . "/core/db/" . $db_type . '.class.php';
		if ($result) {
			// If we require database or user creation we need to connect as root, so we need root login credentials
			if (!empty($db_create_database) && !$userroot) {
				$error_msg = $error_msg . $langs->trans("YouAskDatabaseCreationSoDolibarrNeedToConnect", $db_name);
				$error_msg = $error_msg . $langs->trans("BecauseConnectionFailedParametersMayBeWrong");
				$error++;
				error_log('YouAskDatabaseCreationSoDolibarrNeedToConnect' . $error_msg);
				return json_encode(
					array(
						"IsSuccess" => false,
						"message" => $error_msg,
					),
					JSON_UNESCAPED_UNICODE
				);

			}
			if (!empty($db_create_user) && !$userroot) {

				$error_msg = $error_msg . $langs->trans("YouAskLoginCreationSoDolibarrNeedToConnect", $db_user);
				$error_msg = $error_msg . $langs->trans("BecauseConnectionFailedParametersMayBeWrong");
				$error++;
				error_log('YouAskLoginCreationSoDolibarrNeedToConnect' . $error_msg);
				return json_encode(
					array(
						"IsSuccess" => false,
						"message" => $error_msg,
					),
					JSON_UNESCAPED_UNICODE
				);

			}

			// If we need root access
			if (!$error && (!empty($db_create_database) || !empty($db_create_user))) {
				$databasefortest = $db_name;
				if (!empty($db_create_database)) {
					if ($db_type == 'mysql' || $db_type == 'mysqli') {
						$databasefortest = 'mysql';
					} elseif ($db_type == 'pgsql') {
						$databasefortest = 'postgres';
					} else {
						$databasefortest = 'master';
					}
				}
				$db = getDoliDBInstance($db_type, $db_host, $userroot, $passroot, $databasefortest, $db_port);

				error_log("databasefortest=" . $databasefortest . " connected=" . $db->connected . " database_selected=" . $db->database_selected, LOG_DEBUG);

				if (empty($db_create_database) && $db->connected && !$db->database_selected) {
					$error_msg = $error_msg . $langs->trans("ErrorConnectedButDatabaseNotFound", $db_name);
					if (!$db->connected) {
						$error_msg = $error_msg . $langs->trans("IfDatabaseNotExistsGoBackAndUncheckCreate");
					}
					$error++;
					error_log('ErrorConnectedButDatabaseNotFound' . $error_msg);
					return json_encode(
						array(
							"IsSuccess" => false,
							"message" => $error_msg,
						),
						JSON_UNESCAPED_UNICODE
					);

				} elseif ($db->error && !(!empty($db_create_database) && $db->connected)) {
					// Note: you may experience error here with message "No such file or directory" when mysql was installed for the first time but not yet launched.
					if ($db->error == "No such file or directory") {
						$error_msg = $error_msg . $langs->trans("ErrorToConnectToMysqlCheckInstance");
					} else {
						$error_msg = $error_msg . $db->error;
					}
					if (!$db->connected) {
						$error_msg = $error_msg . $langs->trans("BecauseConnectionFailedParametersMayBeWrong");
					}
					$error++;
					error_log('ErrorToConnectToMysqlCheckInstance' . $error_msg);
					return json_encode(
						array(
							"IsSuccess" => false,
							"message" => $error_msg,
						),
						JSON_UNESCAPED_UNICODE
					);

				}
			}

			// If we need simple access
			if (!$error && (empty($db_create_database) && empty($db_create_user))) {
				$db = getDoliDBInstance($db_type, $db_host, $db_user, $db_pass, $db_name, $db_port);

				if ($db->error) {
					print '<div class="error">' . $db->error . '</div>';
					if (!$db->connected) {
						$error_msg = $error_msg . $langs->trans("BecauseConnectionFailedParametersMayBeWrong");
					}
					$error++;
					error_log('BecauseConnectionFailedParametersMayBeWrong' . $error_msg);
					return json_encode(
						array(
							"IsSuccess" => false,
							"message" => $error_msg,
						),
						JSON_UNESCAPED_UNICODE
					);

				}

			}
		} else {
			$error_msg = $error_msg . "<br>\nFailed to include_once(\"" . $main_dir . "/core/db/" . $db_type . ".class.php\")<br>\n";
			$error++;
			error_log('Failed to include_once' . $error_msg);
			return json_encode(
				array(
					"IsSuccess" => false,
					"message" => $error_msg,
				),
				JSON_UNESCAPED_UNICODE
			);

		}
	} else {
		if (isset($db)) {
			$error_msg = $error_msg . $db->lasterror();
		}
		if (isset($db) && !$db->connected) {
			$error_msg = $error_msg . $langs->trans("BecauseConnectionFailedParametersMayBeWrong");
		}
		$error++;
		return json_encode(
			array(
				"IsSuccess" => false,
				"message" => $error_msg,
			),
			JSON_UNESCAPED_UNICODE
		);

	}

	if (!$error && $db->connected) {
		if (!empty($db_create_database)) {
			$result = $db->select_db($db_name);
			if ($result) {
				$error_msg = $error_msg . $langs->trans("ErrorDatabaseAlreadyExists", $db_name);
				$error++;
				error_log('ErrorDatabaseAlreadyExists' . $error_msg);
				return json_encode(
					array(
						"IsSuccess" => false,
						"message" => $error_msg,
					),
					JSON_UNESCAPED_UNICODE
				);

			}
		}
	}
	// Define $defaultCharacterSet and $defaultDBSortingCollation
	global $defaultCharacterSet;
	global $defaultDBSortingCollation;
	if (!$error && $db->connected) {
		if (!empty($db_create_database)) { // If we create database, we force default value
			// Default values come from the database handler

			$defaultCharacterSet = $db->forcecharset;
			$defaultDBSortingCollation = $db->forcecollate;
		} else // If already created, we take current value
		{
			$defaultCharacterSet = $db->getDefaultCharacterSetDatabase();
			$defaultDBSortingCollation = $db->getDefaultCollationDatabase();
		}

		// Force to avoid utf8mb4 because index on field char 255 reach limit of 767 char for indexes (example with mysql 5.6.34 = mariadb 10.0.29)
		// TODO Remove this when utf8mb4 is supported
		if ($defaultCharacterSet == 'utf8mb4' || $defaultDBSortingCollation == 'utf8mb4_unicode_ci') {
			$defaultCharacterSet = 'utf8';
			$defaultDBSortingCollation = 'utf8_unicode_ci';
		}

		$db_character_set = $defaultCharacterSet;
		$db_collation = $defaultDBSortingCollation;
		error_log("step1: db_character_set=" . $db_character_set . " db_collation=" . $db_collation);
	}


	// Create config file
	if (!$error && $db->connected && $action == "set") {
		umask(0);
		if (is_array($_POST)) {
			foreach ($_POST as $key => $value) {
				if (!preg_match('/^db_pass/i', $key)) {
					error_log("step1: choice for " . $key . " = " . $value);
				}
			}
		}

		// Check parameter main_dir
		if (!$error) {
			if (!is_dir($main_dir)) {
				error_log("step1: directory '" . $main_dir . "' is unavailable or can't be accessed");

				$error_msg = $error_msg . $langs->trans("ErrorDirDoesNotExists", $main_dir);
				$error_msg = $error_msg . $langs->trans("ErrorWrongValueForParameter", $langs->transnoentitiesnoconv("WebPagesDirectory"));
				$error_msg = $error_msg . $langs->trans("ErrorGoBackAndCorrectParameters");
				error_log('ErrorDirDoesNotExists' . $error_msg);
				$error++;
				return json_encode(
					array(
						"IsSuccess" => false,
						"message" => $error_msg,
					),
					JSON_UNESCAPED_UNICODE
				);

			}
		}

		if (!$error) {
			error_log("step1: directory '" . $main_dir . "' exists");
		}


		// Create subdirectory main_data_dir
		if (!$error) {
			// Create directory for documents
			if (!is_dir($main_data_dir)) {
				dol_mkdir($main_data_dir);
			}

			if (!is_dir($main_data_dir)) {
				$error_msg = $error_msg . $langs->trans("ErrorDirDoesNotExists", $main_data_dir) . $langs->trans("ErrorDirDoesNotExists", $main_data_dir) . $langs->trans("YouMustCreateItAndAllowServerToWrite") . $langs->trans("CorrectProblemAndReloadPage", $_SERVER['PHP_SELF'] . '?testget=ok');
				$error++;
				error_log('ErrorDirDoesNotExists' . $error_msg);
				return json_encode(
					array(
						"IsSuccess" => false,
						"message" => $error_msg,
					),
					JSON_UNESCAPED_UNICODE
				);

			} else {
				// Create .htaccess file in document directory
				$pathhtaccess = $main_data_dir . '/.htaccess';
				if (!file_exists($pathhtaccess)) {
					error_log("step1: .htaccess file did not exist, we created it in '" . $main_data_dir . "'");
					$handlehtaccess = @fopen($pathhtaccess, 'w');
					if ($handlehtaccess) {
						fwrite($handlehtaccess, 'Order allow,deny' . "\n");
						fwrite($handlehtaccess, 'Deny from all' . "\n");

						fclose($handlehtaccess);
						error_log("step1: .htaccess file created");
					}
				}

				// Documents are stored above the web pages root to prevent being downloaded without authentification
				$dir = array();
				$dir[] = $main_data_dir . "/mycompany";
				$dir[] = $main_data_dir . "/medias";
				$dir[] = $main_data_dir . "/users";
				$dir[] = $main_data_dir . "/facture";
				$dir[] = $main_data_dir . "/propale";
				$dir[] = $main_data_dir . "/ficheinter";
				$dir[] = $main_data_dir . "/produit";
				$dir[] = $main_data_dir . "/doctemplates";

				// Loop on each directory of dir [] to create them if they do not exist
				$num = count($dir);
				for ($i = 0; $i < $num; $i++) {
					if (is_dir($dir[$i])) {
						error_log("step1: directory '" . $dir[$i] . "' exists");
					} else {
						if (dol_mkdir($dir[$i]) < 0) {
							$error_msg = $error_msg . "Failed to create directory: " . $dir[$i];
							$error++;
							error_log('Failed to create directory:' . $error_msg);
							return json_encode(
								array(
									"IsSuccess" => false,
									"message" => $error_msg,
								),
								JSON_UNESCAPED_UNICODE
							);

						} else {
							error_log("step1: directory '" . $dir[$i] . "' created");
						}
					}
				}
				require_once DOL_DOCUMENT_ROOT . '/core/lib/files.lib.php';
				// Copy directory medias
				$srcroot = $main_dir . '/install/medias';
				$destroot = $main_data_dir . '/medias';
				dolCopyDir($srcroot, $destroot, 0, 0);

				if ($error) {
					$error_msg = $error_msg . $langs->trans("ErrorDirDoesNotExists", $main_data_dir) . $langs->trans("YouMustCreateItAndAllowServerToWrite") . $langs->trans("CorrectProblemAndReloadPage", $_SERVER['PHP_SELF'] . '?testget=ok') . '</td></tr>';
					error_log('YouMustCreateItAndAllowServerToWrite:' . $error_msg);
					return json_encode(
						array(
							"IsSuccess" => false,
							"message" => $error_msg,
						),
						JSON_UNESCAPED_UNICODE
					);

				} else {
					//ODT templates
					$srcroot = $main_dir . '/install/doctemplates';
					$destroot = $main_data_dir . '/doctemplates';
					$docs = array(
						'contracts' => 'contract',
						'invoices' => 'invoice',
						'orders' => 'order',
						'products' => 'product',
						'projects' => 'project',
						'proposals' => 'proposal',
						'shipments' => 'shipment',
						'supplier_proposals' => 'supplier_proposal',
						'tasks' => 'task_summary',
						'thirdparties' => 'thirdparty',
						'usergroups' => 'usergroups',
						'users' => 'user',
					);
					foreach ($docs as $cursordir => $cursorfile) {
						$src = $srcroot . '/' . $cursordir . '/template_' . $cursorfile . '.odt';
						$dirodt = $destroot . '/' . $cursordir;
						$dest = $dirodt . '/template_' . $cursorfile . '.odt';

						dol_mkdir($dirodt);
						$result = dol_copy($src, $dest, 0, 0);
						if ($result < 0) {
							$error_msg = $error_msg . $langs->trans('ErrorFailToCopyFile', $src, $dest);
							error_log('ErrorFailToCopyFile:' . $error_msg);
							return json_encode(
								array(
									"IsSuccess" => false,
									"message" => $error_msg,
								),
								JSON_UNESCAPED_UNICODE
							);

						}
					}
				}
			}
		}

		global $db_prefix;

		// Table prefix
		// Write conf file on disk
		if (!$error) {
			// Save old conf file on disk
			if (file_exists("$conffile")) {
				// We must ignore errors as an existing old file may already exist and not be replaceable or
				// the installer (like for ubuntu) may not have permission to create another file than conf.php.
				// Also no other process must be able to read file or we expose the new file, so content with password.
				@dol_copy($conffile, $conffile . '.old', '0400');
			}
			error_log('[api_init.php]before write_conf_file $conf->file->dol_document_root=' . print_r($conf->file->dol_document_root, true));
			$error += write_conf_file($conffile);
			error_log('after write_conf_file $conf->file->dol_document_root=' . print_r($conf->file->dol_document_root, true));
		}

		// Create database and admin user database
		if (!$error) {
			// We reload configuration file
			global $dolibarr_main_document_root;
			conf($dolibarr_main_document_root);

			$error_msg = $error_msg . $langs->trans("ConfFileReload");
			// Create database user if requested
			if (isset($db_create_user) && ($db_create_user == "1" || $db_create_user == "on")) {
				error_log("step1: create database user: " . $dolibarr_main_db_user);

				//print $conf->db->host." , ".$conf->db->name." , ".$conf->db->user." , ".$conf->db->port;
				$databasefortest = $conf->db->name;
				if ($conf->db->type == 'mysql' || $conf->db->type == 'mysqli') {
					$databasefortest = 'mysql';
				} elseif ($conf->db->type == 'pgsql') {
					$databasefortest = 'postgres';
				} elseif ($conf->db->type == 'mssql') {
					$databasefortest = 'master';
				}

				// Check database connection

				$db = getDoliDBInstance($conf->db->type, $conf->db->host, $userroot, $passroot, $databasefortest, $conf->db->port);

				if ($db->error) {
					$error_msg = $error_msg . $db->error;
					$error++;
					error_log('db->error:' . $error_msg);
					return json_encode(
						array(
							"IsSuccess" => false,
							"message" => $error_msg,
						),
						JSON_UNESCAPED_UNICODE
					);

				}

				if (!$error) {
					if ($db->connected) {
						$resultbis = 1;

						if (empty($dolibarr_main_db_pass)) {
							error_log("step1: failed to create user, password is empty", LOG_ERR);
							$error_msg = $error_msg . $langs->trans("UserCreation") . ": A password for database user is mandatory.";
							error_log('failed to create user, password is empty:' . $error_msg);
							return json_encode(
								array(
									"IsSuccess" => false,
									"message" => $error_msg,
								),
								JSON_UNESCAPED_UNICODE
							);

						} else {
							// Create user
							$result = $db->DDLCreateUser($dolibarr_main_db_host, $dolibarr_main_db_user, $dolibarr_main_db_pass, $dolibarr_main_db_name);

							// Create user bis
							if ($databasefortest == 'mysql') {
								if (!in_array($dolibarr_main_db_host, array('127.0.0.1', '::1', 'localhost', 'localhost.local'))) {
									$resultbis = $db->DDLCreateUser('%', $dolibarr_main_db_user, $dolibarr_main_db_pass, $dolibarr_main_db_name);
								}
							}

							if ($result > 0 && $resultbis > 0) {
								$error_msg = $error_msg . $langs->trans("UserCreation") . ' : ' . $dolibarr_main_db_user;
							} else {
								if (
									$db->errno() == 'DB_ERROR_RECORD_ALREADY_EXISTS'
									|| $db->errno() == 'DB_ERROR_KEY_NAME_ALREADY_EXISTS'
									|| $db->errno() == 'DB_ERROR_USER_ALREADY_EXISTS'
								) {
									error_log("step1: user already exists");
									$error_msg = $error_msg . $langs->trans("UserCreation") . ' : ' . $dolibarr_main_db_user . $langs->trans("LoginAlreadyExists");
								} else {
									error_log("step1: failed to create user", LOG_ERR);
									$error_msg = $error_msg . $langs->trans("UserCreation") . ' : ' . $dolibarr_main_db_user . $langs->trans("Error") . ': ' . $db->errno() . ' ' . $db->error() . ($db->error ? '. ' . $db->error : '');
								}
							}
						}

						$db->close();
					} else {
						$error_msg = $error_msg . $langs->trans("UserCreation") . ' : ' . $dolibarr_main_db_user;
						// warning message due to connection failure
						$error_msg = $error_msg . $langs->trans("YouAskDatabaseCreationSoDolibarrNeedToConnect", $dolibarr_main_db_user, $dolibarr_main_db_host, $userroot) . $langs->trans("BecauseConnectionFailedParametersMayBeWrong") . '<br><br>' . $langs->trans("ErrorGoBackAndCorrectParameters");
						$error++;
						error_log('YouAskDatabaseCreationSoDolibarrNeedToConnect:' . $error_msg);
						return json_encode(
							array(
								"IsSuccess" => false,
								"message" => $error_msg,
							),
							JSON_UNESCAPED_UNICODE
						);

					}
				}
			} // end of user account creation


			// If database creation was asked, we create it
			if (!$error && (isset($db_create_database) && ($db_create_database == "1" || $db_create_database == "on"))) {
				global $dolibarr_main_db_name, $dolibarr_main_db_character_set, $dolibarr_main_db_collation,
				$dolibarr_main_db_user;
				error_log("step1: create database: " . $dolibarr_main_db_name . " " . $dolibarr_main_db_character_set . " " . $dolibarr_main_db_collation . " " . $dolibarr_main_db_user);

				$newdb = getDoliDBInstance($conf->db->type, $conf->db->host, $userroot, $passroot, '', $conf->db->port);

				if ($newdb->connected) {
					$result = $newdb->DDLCreateDb($dolibarr_main_db_name, $dolibarr_main_db_character_set, $dolibarr_main_db_collation, $dolibarr_main_db_user);

					if ($result) {
						$error_msg = $error_msg . $langs->trans("DatabaseCreation") . " (" . $langs->trans("User") . " " . $userroot . ") : " . $dolibarr_main_db_name;
						$newdb->select_db($dolibarr_main_db_name);
						$check1 = $newdb->getDefaultCharacterSetDatabase();
						$check2 = $newdb->getDefaultCollationDatabase();
						error_log('step1: new database is using charset=' . $check1 . ' collation=' . $check2);

					} else {
						// warning message
						$error_msg = $error_msg . $langs->trans("ErrorFailedToCreateDatabase", $dolibarr_main_db_name) . $newdb->lasterror() . $langs->trans("IfDatabaseExistsGoBackAndCheckCreate");

						error_log('step1: failed to create database ' . $dolibarr_main_db_name . ' ' . $newdb->lasterrno() . ' ' . $newdb->lasterror(), LOG_ERR);
						$error++;
						return json_encode(
							array(
								"IsSuccess" => false,
								"message" => $error_msg,
							),
							JSON_UNESCAPED_UNICODE
						);

					}
					$newdb->close();
				} else {
					$error_msg = $error_msg . $langs->trans("DatabaseCreation") . " (" . $langs->trans("User") . " " . $userroot . ") : " . $dolibarr_main_db_name;
					// warning message
					$error_msg = $error_msg . $langs->trans("YouAskDatabaseCreationSoDolibarrNeedToConnect", $dolibarr_main_db_user, $dolibarr_main_db_host, $userroot) . $langs->trans("BecauseConnectionFailedParametersMayBeWrong") . $langs->trans("ErrorGoBackAndCorrectParameters");
					$error++;
					error_log('BecauseConnectionFailedParametersMayBeWrong' . $error_msg);
					return json_encode(
						array(
							"IsSuccess" => false,
							"message" => $error_msg,
						),
						JSON_UNESCAPED_UNICODE
					);

				}
			} // end of create database


			// We test access with dolibarr database user (not admin)
			if (!$error) {
				error_log("step1: connection type=" . $conf->db->type . " on host=" . $conf->db->host . " port=" . $conf->db->port . " user=" . $conf->db->user . " name=" . $conf->db->name);
				//print "connexion de type=".$conf->db->type." sur host=".$conf->db->host." port=".$conf->db->port." user=".$conf->db->user." name=".$conf->db->name;

				$db = getDoliDBInstance($conf->db->type, $conf->db->host, $conf->db->user, $conf->db->pass, $conf->db->name, $conf->db->port);

				if ($db->connected) {
					global $dolibarr_main_db_host;
					error_log("step1: connection to server by user " . $conf->db->user . " ok");
					$error_msg = $error_msg . $langs->trans("ServerConnection") . " (" . $langs->trans("User") . " " . $conf->db->user . ") : " . $dolibarr_main_db_host;
					// server access ok, basic access ok
					if ($db->database_selected) {
						error_log("step1: connection to database " . $conf->db->name . " by user " . $conf->db->user . " ok");
						$error_msg = $error_msg . $langs->trans("DatabaseConnection") . " (" . $langs->trans("User") . " " . $conf->db->user . ") : " . $dolibarr_main_db_name;
						$error = 0;
					} else {
						error_log("step1: connection to database " . $conf->db->name . " by user " . $conf->db->user . " failed", LOG_ERR);
						$error_msg = $error_msg . $langs->trans("DatabaseConnection") . " (" . $langs->trans("User") . " " . $conf->db->user . ") : " . $dolibarr_main_db_name;
						// warning message
						$error_msg = $error_msg . $langs->trans('CheckThatDatabasenameIsCorrect', $dolibarr_main_db_name) . $langs->trans('IfAlreadyExistsCheckOption') . $langs->trans("ErrorGoBackAndCorrectParameters");
						$error++;
						error_log('CheckThatDatabasenameIsCorrect' . $error_msg);
						return json_encode(
							array(
								"IsSuccess" => false,
								"message" => $error_msg,
							),
							JSON_UNESCAPED_UNICODE
						);

					}
				} else {
					error_log("step1: connection to server by user " . $conf->db->user . " failed", LOG_ERR);

					// warning message
					$error_msg = $error_msg . $langs->trans("ServerConnection") . " (" . $langs->trans("User") . " " . $conf->db->user . ") : " . $dolibarr_main_db_host . $langs->trans("ErrorConnection", $conf->db->host, $conf->db->name, $conf->db->user) . $langs->trans('IfLoginDoesNotExistsCheckCreateUser') . $langs->trans("ErrorGoBackAndCorrectParameters");
					$error++;
					error_log('ServerConnection' . $error_msg);
					return json_encode(
						array(
							"IsSuccess" => false,
							"message" => $error_msg,
						),
						JSON_UNESCAPED_UNICODE
					);

				}
			}
		}
	}

	return $error;
}

function write_conf_file($conffile)
{
	global $conf, $langs;
	error_log('in write_conf_file $conf->file->dol_document_root=' . print_r($conf->file->dol_document_root, true));
	global $main_url, $main_dir, $main_data_dir, $main_force_https, $main_use_alt_dir, $main_alt_dir_name, $main_db_prefix;
	global $dolibarr_main_url_root, $dolibarr_main_document_root, $dolibarr_main_data_root,
	$dolibarr_main_db_host;
	global $dolibarr_main_db_port, $dolibarr_main_db_name, $dolibarr_main_db_user, $dolibarr_main_db_pass;
	global $dolibarr_main_db_type, $dolibarr_main_db_character_set, $dolibarr_main_db_collation, $dolibarr_main_authentication;
	global $dolibarr_main_distrib;
	global $db_host, $db_port, $db_name, $db_user, $db_pass, $db_type, $db_character_set, $db_collation;
	global $conffile, $conffiletoshow, $conffiletoshowshort;
	global $force_dolibarr_lib_NUSOAP_PATH;
	global $force_dolibarr_lib_TCPDF_PATH, $force_dolibarr_lib_FPDI_PATH;
	global $force_dolibarr_lib_GEOIP_PATH;
	global $force_dolibarr_lib_ODTPHP_PATH, $force_dolibarr_lib_ODTPHP_PATHTOPCLZIP;
	global $force_dolibarr_js_CKEDITOR, $force_dolibarr_js_JQUERY, $force_dolibarr_js_JQUERY_UI;
	global $force_dolibarr_font_DOL_DEFAULT_TTF, $force_dolibarr_font_DOL_DEFAULT_TTF_BOLD;

	$error = 0;

	$key = md5(uniqid(mt_rand(), true)); // Generate random hash

	$fp = fopen("$conffile", "w");
	if ($fp) {
		clearstatcache();

		fputs($fp, '<?php' . "\n");
		fputs($fp, '//' . "\n");
		fputs($fp, '// File generated by Dolibarr installer ' . DOL_VERSION . ' on ' . dol_now() . "\n");
		fputs($fp, '//' . "\n");
		fputs($fp, '// Take a look at conf.php.example file for an example of ' . $conffiletoshowshort . ' file' . "\n");
		fputs($fp, '// and explanations for all possibles parameters.' . "\n");
		fputs($fp, '//' . "\n");

		fputs($fp, '$dolibarr_main_url_root=\'' . str_replace("'", "\'", trim($main_url)) . '\';');
		fputs($fp, "\n");

		fputs($fp, '$dolibarr_main_document_root=\'' . str_replace("'", "\'", trim($main_dir)) . '\';');
		fputs($fp, "\n");

		fputs($fp, $main_use_alt_dir . '$dolibarr_main_url_root_alt=\'' . str_replace("'", "\'", trim("/" . $main_alt_dir_name)) . '\';');
		fputs($fp, "\n");

		fputs($fp, $main_use_alt_dir . '$dolibarr_main_document_root_alt=\'' . str_replace("'", "\'", trim($main_dir . "/" . $main_alt_dir_name)) . '\';');
		fputs($fp, "\n");

		fputs($fp, '$dolibarr_main_data_root=\'' . str_replace("'", "\'", trim($main_data_dir)) . '\';');
		fputs($fp, "\n");

		fputs($fp, '$dolibarr_main_db_host=\'' . str_replace("'", "\'", trim($db_host)) . '\';');
		fputs($fp, "\n");

		fputs($fp, '$dolibarr_main_db_port=\'' . str_replace("'", "\'", trim($db_port)) . '\';');
		fputs($fp, "\n");

		fputs($fp, '$dolibarr_main_db_name=\'' . str_replace("'", "\'", trim($db_name)) . '\';');
		fputs($fp, "\n");

		fputs($fp, '$dolibarr_main_db_prefix=\'' . str_replace("'", "\'", trim($main_db_prefix)) . '\';');
		fputs($fp, "\n");

		fputs($fp, '$dolibarr_main_db_user=\'' . str_replace("'", "\'", trim($db_user)) . '\';');
		fputs($fp, "\n");
		fputs($fp, '$dolibarr_main_db_pass=\'' . str_replace("'", "\'", trim($db_pass)) . '\';');
		fputs($fp, "\n");

		fputs($fp, '$dolibarr_main_db_type=\'' . str_replace("'", "\'", trim($db_type)) . '\';');
		fputs($fp, "\n");

		fputs($fp, '$dolibarr_main_db_character_set=\'' . str_replace("'", "\'", trim('utf8mb4')) . '\';');
		fputs($fp, "\n");

		fputs($fp, '$dolibarr_main_db_collation=\'' . str_replace("'", "\'", trim('utf8mb4_general_ci')) . '\';');
		fputs($fp, "\n");

		// Authentication
		fputs($fp, '// Authentication settings');
		fputs($fp, "\n");

		fputs($fp, '$dolibarr_main_authentication=\'dolibarr\';');
		fputs($fp, "\n\n");

		fputs($fp, '//$dolibarr_main_demo=\'autologin,autopass\';');
		fputs($fp, "\n");

		fputs($fp, '// Security settings');
		fputs($fp, "\n");

		fputs($fp, '$dolibarr_main_prod=\'0\';');
		fputs($fp, "\n");

		fputs($fp, '$dolibarr_main_force_https=\'' . $main_force_https . '\';');
		fputs($fp, "\n");

		fputs($fp, '$dolibarr_main_restrict_os_commands=\'mysqldump, mysql, pg_dump, pgrestore, clamdscan, clamscan.exe\';');
		fputs($fp, "\n");

		fputs($fp, '$dolibarr_nocsrfcheck=\'0\';');
		fputs($fp, "\n");

		fputs($fp, '$dolibarr_main_instance_unique_id=\'' . $key . '\';');
		fputs($fp, "\n");

		fputs($fp, '$dolibarr_mailing_limit_sendbyweb=\'0\';');
		fputs($fp, "\n");
		fputs($fp, '$dolibarr_mailing_limit_sendbycli=\'0\';');
		fputs($fp, "\n");

		// Write params to overwrites default lib path
		fputs($fp, "\n");
		if (empty($force_dolibarr_lib_FPDF_PATH)) {
			fputs($fp, '//');
			$force_dolibarr_lib_FPDF_PATH = '';
		}
		fputs($fp, '$dolibarr_lib_FPDF_PATH=\'' . $force_dolibarr_lib_FPDF_PATH . '\';');
		fputs($fp, "\n");
		if (empty($force_dolibarr_lib_TCPDF_PATH)) {
			fputs($fp, '//');
			$force_dolibarr_lib_TCPDF_PATH = '';
		}
		fputs($fp, '$dolibarr_lib_TCPDF_PATH=\'' . $force_dolibarr_lib_TCPDF_PATH . '\';');
		fputs($fp, "\n");
		if (empty($force_dolibarr_lib_FPDI_PATH)) {
			fputs($fp, '//');
			$force_dolibarr_lib_FPDI_PATH = '';
		}
		fputs($fp, '$dolibarr_lib_FPDI_PATH=\'' . $force_dolibarr_lib_FPDI_PATH . '\';');
		fputs($fp, "\n");
		if (empty($force_dolibarr_lib_TCPDI_PATH)) {
			fputs($fp, '//');
			$force_dolibarr_lib_TCPDI_PATH = '';
		}
		fputs($fp, '$dolibarr_lib_TCPDI_PATH=\'' . $force_dolibarr_lib_TCPDI_PATH . '\';');
		fputs($fp, "\n");
		if (empty($force_dolibarr_lib_GEOIP_PATH)) {
			fputs($fp, '//');
			$force_dolibarr_lib_GEOIP_PATH = '';
		}
		fputs($fp, '$dolibarr_lib_GEOIP_PATH=\'' . $force_dolibarr_lib_GEOIP_PATH . '\';');
		fputs($fp, "\n");
		if (empty($force_dolibarr_lib_NUSOAP_PATH)) {
			fputs($fp, '//');
			$force_dolibarr_lib_NUSOAP_PATH = '';
		}
		fputs($fp, '$dolibarr_lib_NUSOAP_PATH=\'' . $force_dolibarr_lib_NUSOAP_PATH . '\';');
		fputs($fp, "\n");
		if (empty($force_dolibarr_lib_ODTPHP_PATH)) {
			fputs($fp, '//');
			$force_dolibarr_lib_ODTPHP_PATH = '';
		}
		fputs($fp, '$dolibarr_lib_ODTPHP_PATH=\'' . $force_dolibarr_lib_ODTPHP_PATH . '\';');
		fputs($fp, "\n");
		if (empty($force_dolibarr_lib_ODTPHP_PATHTOPCLZIP)) {
			fputs($fp, '//');
			$force_dolibarr_lib_ODTPHP_PATHTOPCLZIP = '';
		}
		fputs($fp, '$dolibarr_lib_ODTPHP_PATHTOPCLZIP=\'' . $force_dolibarr_lib_ODTPHP_PATHTOPCLZIP . '\';');
		fputs($fp, "\n");
		if (empty($force_dolibarr_js_CKEDITOR)) {
			fputs($fp, '//');
			$force_dolibarr_js_CKEDITOR = '';
		}
		fputs($fp, '$dolibarr_js_CKEDITOR=\'' . $force_dolibarr_js_CKEDITOR . '\';');
		fputs($fp, "\n");
		if (empty($force_dolibarr_js_JQUERY)) {
			fputs($fp, '//');
			$force_dolibarr_js_JQUERY = '';
		}
		fputs($fp, '$dolibarr_js_JQUERY=\'' . $force_dolibarr_js_JQUERY . '\';');
		fputs($fp, "\n");
		if (empty($force_dolibarr_js_JQUERY_UI)) {
			fputs($fp, '//');
			$force_dolibarr_js_JQUERY_UI = '';
		}
		fputs($fp, '$dolibarr_js_JQUERY_UI=\'' . $force_dolibarr_js_JQUERY_UI . '\';');
		fputs($fp, "\n");

		// Write params to overwrites default font path
		fputs($fp, "\n");
		if (empty($force_dolibarr_font_DOL_DEFAULT_TTF)) {
			fputs($fp, '//');
			$force_dolibarr_font_DOL_DEFAULT_TTF = '';
		}
		fputs($fp, '$dolibarr_font_DOL_DEFAULT_TTF=\'' . $force_dolibarr_font_DOL_DEFAULT_TTF . '\';');
		fputs($fp, "\n");
		if (empty($force_dolibarr_font_DOL_DEFAULT_TTF_BOLD)) {
			fputs($fp, '//');
			$force_dolibarr_font_DOL_DEFAULT_TTF_BOLD = '';
		}
		fputs($fp, '$dolibarr_font_DOL_DEFAULT_TTF_BOLD=\'' . $force_dolibarr_font_DOL_DEFAULT_TTF_BOLD . '\';');
		fputs($fp, "\n");

		// Other
		fputs($fp, '$dolibarr_main_distrib=\'' . str_replace("'", "\'", trim($dolibarr_main_distrib)) . '\';');
		fputs($fp, "\n");

		fclose($fp);

		if (file_exists("$conffile")) {
			include $conffile; // force config reload, do not put include_once
			error_log('in write_conf_file $dolibarr_main_document_root=' . $dolibarr_main_document_root);
			error_log('in write_conf before conf() $conf->file->dol_document_root='.print_r($conf->file->dol_document_root,true));
			conf($dolibarr_main_document_root);
			error_log('in write_conf_file after conf() $conf->file->dol_document_root=' . print_r($conf->file->dol_document_root, true));
		} else {
			$error++;
		}
	}

	return $error;
}

function step2($comp)
{
	error_log('[api_ini.php]begin step2...');
	global $dolibarr_main_document_root;
	require_once $dolibarr_main_document_root . '/core/class/conf.class.php';
	require_once $dolibarr_main_document_root . '/core/lib/admin.lib.php';

	global $langs;

	$step = 2;
	$ok = 0;
	$error_msg = '';

	$err = error_reporting();
	error_reporting(0); // Disable all errors
//error_reporting(E_ALL);
	@set_time_limit(1800); // Need 1800 on some very slow OS like Windows 7/64
	error_reporting($err);

	global $action;
	global $setuplang;
	$langs->setDefaultLang($setuplang);
	$langs->loadLangs(array("admin", "install"));

	// Choice of DBMS
	global $dolibarr_main_db_type;
	error_log('$dolibarr_main_db_type:' . $dolibarr_main_db_type);
	$choix = 0;
	if ($dolibarr_main_db_type == "mysqli") {
		$choix = 1;
	}
	if ($dolibarr_main_db_type == "pgsql") {
		$choix = 2;
	}
	if ($dolibarr_main_db_type == "mssql") {
		$choix = 3;
	}
	if ($dolibarr_main_db_type == "sqlite") {
		$choix = 4;
	}
	if ($dolibarr_main_db_type == "sqlite3") {
		$choix = 5;
	}

	$useforcedwizard = false;
	$forcedfile = "./install.forced.php";
	global $conffile;
	error_log('$conffile:' . $conffile);
	if ($conffile == "/etc/dolibarr/conf.php") {
		$forcedfile = "/etc/dolibarr/install.forced.php";
	}
	if (@file_exists($forcedfile)) {
		$useforcedwizard = true;
		include_once $forcedfile;
	}

	error_log("- step2: entering step2.php page");


	// Test if we can run a first install process
	if (!is_writable($conffile)) {
		$error_msg = $error_msg . $langs->trans("ConfFileIsNotWritable", $conffiletoshow);
		error_log('ConfFileIsNotWritable:' . $error_msg);
		return json_encode(
			array(
				"IsSuccess" => false,
				"message" => $error_msg,
			),
			JSON_UNESCAPED_UNICODE
		);
	}
	$error = 0;
	global $conf;
	if ($action == "set") {
		$db = getDoliDBInstance($conf->db->type, $conf->db->host, $conf->db->user, $conf->db->pass, $conf->db->name, $conf->db->port);

		if ($db->connected) {
			error_log($langs->trans("ServerConnection") . " : " . $conf->db->host);
			$ok = 1;
		} else {

			$error_msg = $error_msg . "Failed to connect to server : " . $conf->db->host;
			error_log('Failed to connect to server :' . $conf->db->host);
			return json_encode(
				array(
					"IsSuccess" => false,
					"message" => $error_msg,
				),
				JSON_UNESCAPED_UNICODE
			);
		}

		if ($ok) {
			if ($db->database_selected) {
				error_log("step2: successful connection to database: " . $conf->db->name);
			} else {
				error_log("step2: failed connection to database :" . $conf->db->name, LOG_ERR);
				print "<tr><td>Failed to select database " . $conf->db->name . '</td><td><img src="../theme/eldy/img/error.png" alt="Error"></td></tr>';
				$error_msg = $error_msg . "Failed to select database " . $conf->db->name;
				$ok = 0;
				return json_encode(
					array(
						"IsSuccess" => false,
						"message" => $error_msg,
					),
					JSON_UNESCAPED_UNICODE
				);
			}
		}


		// Display version / Affiche version
		if ($ok) {
			$version = $db->getVersion();
			$versionarray = $db->getVersionArray();
			error_log($langs->trans("DatabaseVersion:") . $version . ',' . $langs->trans("DatabaseName") . ':' . $db->database_name);

		}

		$requestnb = 0;

		// To disable some code, so you can call step2 with url like
		// http://localhost/dolibarrnew/install/step2.php?action=set&token='.newToken().'&createtables=0&createkeys=0&createfunctions=0&createdata=llx_20_c_departements
		$createtables = GETPOSTISSET('createtables') ? GETPOST('createtables') : 1;
		$createkeys = GETPOSTISSET('createkeys') ? GETPOST('createkeys') : 1;
		$createfunctions = GETPOSTISSET('createfunctions') ? GETPOST('createfunction') : 1;
		$createdata = GETPOSTISSET('createdata') ? GETPOST('createdata') : 1;

		// To say sql requests are escaped for mysql so we need to unescape them
		$db->unescapeslashquot = true;

		/**************************************************************************************
		 *
		 * Load files tables/*.sql (not the *.key.sql). Files with '-xxx' in name are excluded (they will be loaded during activation of module 'xxx').
		 * To do before the files *.key.sql
		 *
		 ***************************************************************************************/
		if ($ok && $createtables) {
			// We always choose in mysql directory (Conversion is done by driver to translate SQL syntax)
			$dir = "mysql/tables/";

			$ok = 0;
			$handle = opendir($dir);
			error_log("step2: open tables directory " . $dir . " handle=" . $handle);
			$tablefound = 0;
			$tabledata = array();
			if (is_resource($handle)) {
				while (($file = readdir($handle)) !== false) {
					if (preg_match('/\.sql$/i', $file) && preg_match('/^llx_/i', $file) && !preg_match('/\.key\.sql$/i', $file) && !preg_match('/\-/', $file)) {
						$tablefound++;
						$tabledata[] = $file;
					}
				}
				closedir($handle);
			}

			// Sort list of sql files on alphabetical order (load order is important)
			sort($tabledata);
			foreach ($tabledata as $file) {
				$name = substr($file, 0, dol_strlen($file) - 4);
				$buffer = '';
				$fp = fopen($dir . $file, "r");
				if ($fp) {
					while (!feof($fp)) {
						$buf = fgets($fp, 4096);
						if (substr($buf, 0, 2) <> '--') {
							$buf = preg_replace('/--(.+)*/', '', $buf);
							$buffer .= $buf;
						}
					}
					fclose($fp);

					$buffer = trim($buffer);
					if ($conf->db->type == 'mysql' || $conf->db->type == 'mysqli') { // For Mysql 5.5+, we must replace type=innodb with ENGINE=innodb
						$buffer = preg_replace('/type=innodb/i', 'ENGINE=innodb', $buffer);
					} else {
						// Keyword ENGINE is MySQL-specific, so scrub it for
						// other database types (mssql, pgsql)
						$buffer = preg_replace('/type=innodb/i', '', $buffer);
						$buffer = preg_replace('/ENGINE=innodb/i', '', $buffer);
					}

					// Replace the prefix tables
					global $dolibarr_main_db_prefix;
					if ($dolibarr_main_db_prefix != 'llx_') {
						$buffer = preg_replace('/llx_/i', $dolibarr_main_db_prefix, $buffer);
					}

					//print "<tr><td>Creation of table $name/td>";
					$requestnb++;

					error_log("step2: request: " . $buffer);
					$resql = $db->query($buffer, 0, 'dml');
					if ($resql) {
						// print "<td>OK request ==== $buffer</td></tr>";
						$db->free($resql);
					} else {
						if (
							$db->errno() == 'DB_ERROR_TABLE_ALREADY_EXISTS' ||
							$db->errno() == 'DB_ERROR_TABLE_OR_KEY_ALREADY_EXISTS'
						) {
							//print "<td>already existing</td></tr>";
						} else {
							$error_msg = $error_msg . $langs->trans("CreateTableAndPrimaryKey", $name) . ',' . $langs->trans("Request") . ' ' . $requestnb . ' : ' . $buffer . ' <br>Executed query : ' . $db->lastquery . ',' . $langs->trans("ErrorSQL") . " " . $db->errno() . " " . $db->error();
							$error++;
							return json_encode(
								array(
									"IsSuccess" => false,
									"message" => $error_msg,
								),
								JSON_UNESCAPED_UNICODE
							);
						}
					}
				} else {
					$error++;
					error_log("step2: failed to open file " . $dir . $file, LOG_ERR);
					$error_msg = $error_msg . $langs->trans("CreateTableAndPrimaryKey", $name) . ',' . $langs->trans("Error") . ' Failed to open file ' . $dir . $file;
					return json_encode(
						array(
							"IsSuccess" => false,
							"message" => $error_msg,
						),
						JSON_UNESCAPED_UNICODE
					);
				}
			}

			if ($tablefound) {
				if ($error == 0) {
					error_log($langs->trans("TablesAndPrimaryKeysCreation"));
					$ok = 1;
				}
			} else {
				error_log("step2: failed to find files to create database in directory " . $dir, LOG_ERR);
				$error_msg = $error_msg . $langs->trans("ErrorFailedToFindSomeFiles", $dir);
				return json_encode(
					array(
						"IsSuccess" => false,
						"message" => $error_msg,
					),
					JSON_UNESCAPED_UNICODE
				);
			}
		}


		/***************************************************************************************
		 *
		 * Load files tables/*.key.sql. Files with '-xxx' in name are excluded (they will be loaded during activation of module 'xxx').
		 * To do after the files *.sql
		 *
		 ***************************************************************************************/
		if ($ok && $createkeys) {
			// We always choose in mysql directory (Conversion is done by driver to translate SQL syntax)
			$dir = "mysql/tables/";

			$okkeys = 0;
			$handle = opendir($dir);
			error_log("step2: open keys directory " . $dir . " handle=" . $handle);
			$tablefound = 0;
			$tabledata = array();
			if (is_resource($handle)) {
				while (($file = readdir($handle)) !== false) {
					if (preg_match('/\.sql$/i', $file) && preg_match('/^llx_/i', $file) && preg_match('/\.key\.sql$/i', $file) && !preg_match('/\-/', $file)) {
						$tablefound++;
						$tabledata[] = $file;
					}
				}
				closedir($handle);
			}

			// Sort list of sql files on alphabetical order (load order is important)
			sort($tabledata);
			foreach ($tabledata as $file) {
				$name = substr($file, 0, dol_strlen($file) - 4);
				//print "<tr><td>Creation of table $name</td>";
				$buffer = '';
				$fp = fopen($dir . $file, "r");
				if ($fp) {
					while (!feof($fp)) {
						$buf = fgets($fp, 4096);

						// Special case of lines allowed for some version only
						// MySQL
						if ($choix == 1 && preg_match('/^--\sV([0-9\.]+)/i', $buf, $reg)) {
							$versioncommande = explode('.', $reg[1]);
							//var_dump($versioncommande);
							//var_dump($versionarray);
							if (
								count($versioncommande) && count($versionarray)
								&& versioncompare($versioncommande, $versionarray) <= 0
							) {
								// Version qualified, delete SQL comments
								$buf = preg_replace('/^--\sV([0-9\.]+)/i', '', $buf);
								//print "Ligne $i qualifiee par version: ".$buf.'<br>';
							}
						}
						// PGSQL
						if ($choix == 2 && preg_match('/^--\sPOSTGRESQL\sV([0-9\.]+)/i', $buf, $reg)) {
							$versioncommande = explode('.', $reg[1]);
							//var_dump($versioncommande);
							//var_dump($versionarray);
							if (
								count($versioncommande) && count($versionarray)
								&& versioncompare($versioncommande, $versionarray) <= 0
							) {
								// Version qualified, delete SQL comments
								$buf = preg_replace('/^--\sPOSTGRESQL\sV([0-9\.]+)/i', '', $buf);
								//print "Ligne $i qualifiee par version: ".$buf.'<br>';
							}
						}

						// Add line if no comment
						if (!preg_match('/^--/i', $buf)) {
							$buffer .= $buf;
						}
					}
					fclose($fp);
					global $dolibarr_main_db_prefix;
					// If several requests, we loop on each
					$listesql = explode(';', $buffer);
					foreach ($listesql as $req) {
						$buffer = trim($req);
						if ($buffer) {
							// Replace the prefix tables
							if ($dolibarr_main_db_prefix != 'llx_') {
								$buffer = preg_replace('/llx_/i', $dolibarr_main_db_prefix, $buffer);
							}

							//print "<tr><td>Creation of keys and table index $name: '$buffer'</td>";
							$requestnb++;

							error_log("step2: request: " . $buffer);
							$resql = $db->query($buffer, 0, 'dml');
							if ($resql) {
								//print "<td>OK request ==== $buffer</td></tr>";
								$db->free($resql);
							} else {
								if (
									$db->errno() == 'DB_ERROR_KEY_NAME_ALREADY_EXISTS' ||
									$db->errno() == 'DB_ERROR_CANNOT_CREATE' ||
									$db->errno() == 'DB_ERROR_PRIMARY_KEY_ALREADY_EXISTS' ||
									$db->errno() == 'DB_ERROR_TABLE_OR_KEY_ALREADY_EXISTS' ||
									preg_match('/duplicate key name/i', $db->error())
								) {
									//print "<td>Deja existante</td></tr>";
									$key_exists = 1;
								} else {
									$error++;
									$error_msg = $error_msg . $langs->trans("CreateOtherKeysForTable", $name) .
										',' . $langs->trans("Request") . ' ' . $requestnb . ' : ' . $db->lastqueryerror() . ',' . $langs->trans("ErrorSQL") . " " . $db->errno() . " " . $db->error();
									error_log($error_msg);
									return json_encode(
										array(
											"IsSuccess" => false,
											"message" => $error_msg,
										),
										JSON_UNESCAPED_UNICODE
									);
								}
							}
						}
					}
				} else {
					$error++;
					error_log("step2: failed to open file " . $dir . $file, LOG_ERR);
					$error_msg = $error_msg . $langs->trans("CreateOtherKeysForTable", $name) . ',' . $langs->trans("Error") . " Failed to open file " . $dir . $file;
					return json_encode(
						array(
							"IsSuccess" => false,
							"message" => $error_msg,
						),
						JSON_UNESCAPED_UNICODE
					);
				}
			}

			if ($tablefound && $error == 0) {
				error_log($langs->trans("OtherKeysCreation"));
				$okkeys = 1;
			}
		}


		/***************************************************************************************
		 *
		 * Load the file 'functions.sql'
		 *
		 ***************************************************************************************/
		if ($ok && $createfunctions) {
			// For this file, we use a directory according to database type
			if ($choix == 1) {
				$dir = "mysql/functions/";
			} elseif ($choix == 2) {
				$dir = "pgsql/functions/";
			} elseif ($choix == 3) {
				$dir = "mssql/functions/";
			} elseif ($choix == 4) {
				$dir = "sqlite3/functions/";
			}

			// Creation of data
			$file = "functions.sql";
			if (file_exists($dir . $file)) {
				$fp = fopen($dir . $file, "r");
				error_log("step2: open function file " . $dir . $file . " handle=" . $fp);
				if ($fp) {
					$buffer = '';
					while (!feof($fp)) {
						$buf = fgets($fp, 4096);
						if (substr($buf, 0, 2) <> '--') {
							$buffer .= $buf . "§";
						}
					}
					fclose($fp);
				}
				//$buffer=preg_replace('/;\';/',";'§",$buffer);

				// If several requests, we loop on each of them
				$listesql = explode('§', $buffer);
				foreach ($listesql as $buffer) {
					$buffer = trim($buffer);
					if ($buffer) {
						// Replace the prefix in table names
						if ($dolibarr_main_db_prefix != 'llx_') {
							$buffer = preg_replace('/llx_/i', $dolibarr_main_db_prefix, $buffer);
						}
						error_log("step2: request: " . $buffer);
						$resql = $db->query($buffer, 0, 'dml');
						if ($resql) {
							$ok = 1;
							$db->free($resql);
						} else {
							if (
								$db->errno() == 'DB_ERROR_RECORD_ALREADY_EXISTS'
								|| $db->errno() == 'DB_ERROR_KEY_NAME_ALREADY_EXISTS'
							) {
								//print "Insert line : ".$buffer."<br>\n";
							} else {
								$ok = 0;
								$error++;
								$error_msg = $error_msg . $langs->trans("FunctionsCreation") . ',' . $langs->trans("Request") . ' ' . $requestnb . ' : ' . $buffer . ',' . $langs->trans("ErrorSQL") . " " . $db->errno() . " " . $db->error();
								error_log($error_msg);
								return json_encode(
									array(
										"IsSuccess" => false,
										"message" => $error_msg,
									),
									JSON_UNESCAPED_UNICODE
								);
							}

						}
					}
				}

				error_log($langs->trans("FunctionsCreation"));
				if ($ok) {
					error_log(':ok');
				} else {
					error_log(':error');
					$ok = 1;
					$error_msg = $error_msg . $langs->trans("FunctionsCreation") . ':error';
					return json_encode(
						array(
							"IsSuccess" => false,
							"message" => $error_msg,
						),
						JSON_UNESCAPED_UNICODE
					);
				}
			}
		}


		/***************************************************************************************
		 *
		 * Load files data/*.sql. Files with '-xxx' in name are excluded (they will be loaded during activation of module 'xxx').
		 *
		 ***************************************************************************************/
		if ($ok && $createdata) {
			// We always choose in mysql directory (Conversion is done by driver to translate SQL syntax)
			$dir = "mysql/data/";

			// Insert data
			$handle = opendir($dir);
			error_log("step2: open directory data " . $dir . " handle=" . $handle);
			$tablefound = 0;
			$tabledata = array();
			if (is_resource($handle)) {
				while (($file = readdir($handle)) !== false) {
					if (preg_match('/\.sql$/i', $file) && preg_match('/^llx_/i', $file) && !preg_match('/\-/', $file)) {
						if (preg_match('/^llx_accounting_account_/', $file)) {
							continue; // We discard data file of chart of account. This will be loaded when a chart is selected.
						}

						//print 'x'.$file.'-'.$createdata.'<br>';
						if (is_numeric($createdata) || preg_match('/' . preg_quote($createdata) . '/i', $file)) {
							$tablefound++;
							$tabledata[] = $file;
						}
					}
				}
				closedir($handle);
			}

			// Sort list of data files on alphabetical order (load order is important)
			sort($tabledata);
			foreach ($tabledata as $file) {
				$name = substr($file, 0, dol_strlen($file) - 4);
				$fp = fopen($dir . $file, "r");
				error_log("step2: open data file " . $dir . $file . " handle=" . $fp);
				if ($fp) {
					$arrayofrequests = array();
					$linefound = 0;
					$linegroup = 0;
					$sizeofgroup = 1; // Grouping request to have 1 query for several requests does not works with mysql, so we use 1.

					// Load all requests
					while (!feof($fp)) {
						$buffer = fgets($fp, 4096);
						$buffer = trim($buffer);
						if ($buffer) {
							if (substr($buffer, 0, 2) == '--') {
								continue;
							}

							if ($linefound && ($linefound % $sizeofgroup) == 0) {
								$linegroup++;
							}
							if (empty($arrayofrequests[$linegroup])) {
								$arrayofrequests[$linegroup] = $buffer;
							} else {
								$arrayofrequests[$linegroup] .= " " . $buffer;
							}

							$linefound++;
						}
					}
					fclose($fp);

					error_log("step2: found " . $linefound . " records, defined " . count($arrayofrequests) . " group(s).");

					$okallfile = 1;
					$db->begin();

					// We loop on each requests of file
					foreach ($arrayofrequests as $buffer) {
						// Replace the tables prefixes
						if ($dolibarr_main_db_prefix != 'llx_') {
							$buffer = preg_replace('/llx_/i', $dolibarr_main_db_prefix, $buffer);
						}

						//error_log("step2: request: " . $buffer);
						$resql = $db->query($buffer, 1);
						if ($resql) {
							//$db->free($resql);     // Not required as request we launch here does not return memory needs.
						} else {
							if ($db->lasterrno() == 'DB_ERROR_RECORD_ALREADY_EXISTS') {
								//print "<tr><td>Insertion ligne : $buffer</td><td>";
							} else {
								$ok = 0;
								$okallfile = 0;
								$error_msg = $error_msg . $langs->trans("ErrorSQL") . " : " . $db->lasterrno() . " - " . $db->lastqueryerror() . " - " . $db->lasterror();
								error_log($error_msg);
								return json_encode(
									array(
										"IsSuccess" => false,
										"message" => $error_msg,
									),
									JSON_UNESCAPED_UNICODE
								);
							}
						}
					}

					if ($okallfile) {
						$db->commit();
					} else {
						$db->rollback();
					}
				}
			}

			error_log($langs->trans("ReferenceDataLoading"));
			$error_msg = $error_msg . $langs->trans("ReferenceDataLoading");
			if ($ok) {
				error_log(':ok');
			} else {
				$error_msg = $error_msg . ':error';
				error_log(':error');
				$ok = 1; // Data loading are not blocking errors
				return json_encode(
					array(
						"IsSuccess" => false,
						"message" => $error_msg,
					),
					JSON_UNESCAPED_UNICODE
				);
			}
		}
	} else {
		error_log('Parameter action=set not defined');
		return json_encode(
			array(
				"IsSuccess" => false,
				"message" => 'Parameter action=set not defined',
			),
			JSON_UNESCAPED_UNICODE
		);
	}


	$ret = 0;
	if (!$ok && isset($argv[1])) {
		$ret = 1;
	}
	error_log("Exit " . $ret);

	error_log("- step2: end");


	// Force here a value we need after because master.inc.php is not loaded into step2.
// This code must be similar with the one into main.inc.php

	$conf->file->instance_unique_id = (empty($dolibarr_main_instance_unique_id) ? (empty($dolibarr_main_cookie_cryptkey) ? '' : $dolibarr_main_cookie_cryptkey) : $dolibarr_main_instance_unique_id); // Unique id of instance

	$hash_unique_id = md5('dolibarr' . $conf->file->instance_unique_id);

	if (isset($db) && is_object($db)) {
		$db->close();
	}

	// Return code if ran from command line
	if ($ret) {
		return ($ret);
	}
	return $error;
}

function step5($comp, $login, $pass)
{
	error_log('[api_ini.php]begin step5...');
	define('ALLOWED_IF_UPGRADE_UNLOCK_FOUND', 1);
	global $conf, $conffile, $dolibarr_main_data_root, $dolibarr_main_document_root, $dolibarr_main_db_pass;
	include_once 'inc.php';
	error_log('conffile:' . $conffile . ' is exists' . file_exists($conffile));
	if (file_exists($conffile)) {
		include_once $conffile;
	}
	error_log('[step5]$dolibarr_main_data_root=' . $dolibarr_main_data_root);
	error_log('[step5] $dolibarr_main_document_root:' . $dolibarr_main_document_root);
	error_log('after include_once conffile $dolibarr_main_db_pass=' . $dolibarr_main_db_pass);
	require_once $dolibarr_main_document_root . '/core/lib/admin.lib.php';
	require_once $dolibarr_main_document_root . '/core/lib/security.lib.php'; // for dol_hash
	require_once $dolibarr_main_document_root . '/core/lib/functions2.lib.php';

	global $langs;

	$versionfrom = GETPOST("versionfrom", 'alpha', 3) ? GETPOST("versionfrom", 'alpha', 3) : (empty($argv[1]) ? '' : $argv[1]);
	$versionto = GETPOST("versionto", 'alpha', 3) ? GETPOST("versionto", 'alpha', 3) : (empty($argv[2]) ? '' : $argv[2]);
	global $setuplang;
	global $langs;
	$langs->setDefaultLang($setuplang);
	global $action;

	// Define targetversion used to update MAIN_VERSION_LAST_INSTALL for first install
// or MAIN_VERSION_LAST_UPGRADE for upgrade.
	$targetversion = DOL_VERSION; // If it's latest upgrade
	if (!empty($action) && preg_match('/upgrade/i', $action)) {
		// If it's an old upgrade
		$tmp = explode('_', $action, 2);
		if ($tmp[0] == 'upgrade') {
			if (!empty($tmp[1])) {
				$targetversion = $tmp[1]; // if $action = 'upgrade_6.0.0-beta', we use '6.0.0-beta'
			} else {
				$targetversion = DOL_VERSION; // if $action = 'upgrade', we use DOL_VERSION
			}
		}
	}

	$langs->loadLangs(array("admin", "install"));

	$pass_verif = $pass;
	global $force_install_lockinstall;
	$force_install_lockinstall = (int) (!empty($force_install_lockinstall) ? $force_install_lockinstall : (GETPOST('installlock', 'aZ09') ? GETPOST('installlock', 'aZ09') : (empty($argv[8]) ? '' : $argv[8])));

	$success = 0;

	$useforcedwizard = false;
	$forcedfile = "./install.forced.php";
	if ($conffile == "/etc/dolibarr/conf.php") {
		$forcedfile = "/etc/dolibarr/install.forced.php";
	}
	if (@file_exists($forcedfile)) {
		$useforcedwizard = true;
		include_once $forcedfile;
		// If forced install is enabled, replace post values. These are empty because form fields are disabled.
		if ($force_install_noedit == 2) {
			if (!empty($force_install_dolibarrlogin)) {
				$login = $force_install_dolibarrlogin;
			}
		}
	}

	error_log("- step5: entering step5.php page");

	$error = 0;
	$error_msg = '';
	/*
	 *	Actions
	 */

	// If install, check password and password_verification used to create admin account
	if ($action == "set") {
		if ($pass <> $pass_verif) {

			return json_encode(
				array(
					"IsSuccess" => false,
					"message" => '$pass <> $pass_verif',
				),
				JSON_UNESCAPED_UNICODE
			);
		}

		if (dol_strlen(trim($pass)) == 0) {
			error_log("Location: step4.php?error=2&selectlang=$setuplang" . (isset($login) ? '&login=' . $login : ''));
			return json_encode(
				array(
					"IsSuccess" => false,
					"message" => 'pass can not be empty',
				),
				JSON_UNESCAPED_UNICODE
			);
		}

		if (dol_strlen(trim($login)) == 0) {
			header("Location: step4.php?error=3&selectlang=$setuplang" . (isset($login) ? '&login=' . $login : ''));
			return json_encode(
				array(
					"IsSuccess" => false,
					"message" => 'login can not be empty',
				),
				JSON_UNESCAPED_UNICODE
			);
		}
	}

	global $conffiletoshow;
	// Test if we can run a first install process
	if (empty($versionfrom) && empty($versionto) && !is_writable($conffile)) {

		$error_msg = $error_msg . $langs->trans("ConfFileIsNotWritable", $conffiletoshow);
		return json_encode(
			array(
				"IsSuccess" => false,
				"message" => $error_msg,
			),
			JSON_UNESCAPED_UNICODE
		);
	}
	if ($action == "set" || empty($action) || preg_match('/upgrade/i', $action)) {
		$error = 0;

		// If password is encoded, we decode it
		if ((!empty($dolibarr_main_db_pass) && preg_match('/crypted:/i', $dolibarr_main_db_pass)) || !empty($dolibarr_main_db_encrypted_pass)) {
			require_once $dolibarr_main_document_root . '/core/lib/security.lib.php';
			if (!empty($dolibarr_main_db_pass) && preg_match('/crypted:/i', $dolibarr_main_db_pass)) {
				$dolibarr_main_db_pass = preg_replace('/crypted:/i', '', $dolibarr_main_db_pass);
				$dolibarr_main_db_pass = dol_decode($dolibarr_main_db_pass);
				$dolibarr_main_db_encrypted_pass = $dolibarr_main_db_pass; // We need to set this as it is used to know the password was initially crypted
			} else {
				$dolibarr_main_db_pass = dol_decode($dolibarr_main_db_encrypted_pass);
			}
		}
		error_log('[api_init.php] before global conf:' . print_r($conf->file->dol_document_root, true));
		global $dolibarr_main_db_type, $dolibarr_main_db_host, $dolibarr_main_db_port
		, $dolibarr_main_db_name, $dolibarr_main_db_user, $dolibarr_main_db_pass;
		error_log('[api_init.php] after global conf:' . print_r($conf->file->dol_document_root, true));
		error_log('$dolibarr_main_db_type:' . $dolibarr_main_db_type);
		error_log('$host:' . $dolibarr_main_db_host);
		error_log('$port:' . $dolibarr_main_db_port);
		error_log('$name:' . $dolibarr_main_db_name);
		error_log('$user:' . $dolibarr_main_db_user);
		error_log('$pass:' . $dolibarr_main_db_pass);
		$conf->db->type = $dolibarr_main_db_type;
		$conf->db->host = $dolibarr_main_db_host;
		$conf->db->port = $dolibarr_main_db_port;
		$conf->db->name = $dolibarr_main_db_name;
		$conf->db->user = $dolibarr_main_db_user;
		$conf->db->pass = $dolibarr_main_db_pass;
		$conf->db->dolibarr_main_db_encryption = isset($dolibarr_main_db_encryption) ? $dolibarr_main_db_encryption : '';
		$conf->db->dolibarr_main_db_cryptkey = isset($dolibarr_main_db_cryptkey) ? $dolibarr_main_db_cryptkey : '';

		$dolibarr_main_data_root = isset($dolibarr_main_data_root) ? trim($dolibarr_main_data_root) : '';
		$dolibarr_main_url_root = isset($dolibarr_main_url_root) ? trim($dolibarr_main_url_root) : '';
		$dolibarr_main_url_root_alt = isset($dolibarr_main_url_root_alt) ? trim($dolibarr_main_url_root_alt) : '';
		$dolibarr_main_document_root = isset($dolibarr_main_document_root) ? trim($dolibarr_main_document_root) : '';
		$dolibarr_main_document_root_alt = isset($dolibarr_main_document_root_alt) ? trim($dolibarr_main_document_root_alt) : '';
		define(DOL_DATA_ROOT, $dolibarr_main_data_root);
		global $db;
		$db = getDoliDBInstance($conf->db->type, $conf->db->host, $conf->db->user, $conf->db->pass, $conf->db->name, $conf->db->port);

		// Create the global $hookmanager object
		include_once DOL_DOCUMENT_ROOT . '/core/class/hookmanager.class.php';
		$hookmanager = new HookManager($db);

		$ok = 0;

		// If first install
		error_log('!!!!!!!!!!!!!!!!!!!');
		error_log('$conf->file->dol_document_root:' . print_r($conf->file->dol_document_root, true));

		if ($action == "set") {
			// Active module user
			$modName = 'modUser';
			$file = $modName . ".class.php";
			error_log('step5: load module user ' . DOL_DOCUMENT_ROOT . "/core/modules/" . $file, LOG_INFO);
			include_once DOL_DOCUMENT_ROOT . "/core/modules/" . $file;
			$objMod = new $modName($db);
			$result = $objMod->init();
			error_log('module init resule:' . $result);
			if (!$result) {
				$error_msg = $error_msg . "ERROR: failed to init module file = " . $file;
				error_log($error_msg);
				return json_encode(
					array(
						"IsSuccess" => false,
						"message" => $error_msg,
					),
					JSON_UNESCAPED_UNICODE
				);
			}

			if ($db->connected) {
				$conf->setValues($db);
				// Reset forced setup after the setValues
				if (defined('SYSLOG_FILE')) {
					$conf->global->SYSLOG_FILE = constant('SYSLOG_FILE');
				}
				$conf->global->MAIN_ENABLE_LOG_TO_HTML = 1;

				// Create admin user
				include_once DOL_DOCUMENT_ROOT . '/user/class/user.class.php';

				// Set default encryption to yes, generate a salt and set default encryption algorythm (but only if there is no user yet into database)
				$sql = "SELECT u.rowid, u.pass, u.pass_crypted";
				$sql .= " FROM " . MAIN_DB_PREFIX . "user as u";
				$resql = $db->query($sql);
				if ($resql) {
					$numrows = $db->num_rows($resql);
					if ($numrows == 0) {
						// Define default setup for password encryption
						dolibarr_set_const($db, "DATABASE_PWD_ENCRYPTED", "1", 'chaine', 0, '', $conf->entity);
						dolibarr_set_const($db, "MAIN_SECURITY_SALT", dol_print_date(dol_now(), 'dayhourlog'), 'chaine', 0, '', 0); // All entities
						if (function_exists('password_hash')) {
							dolibarr_set_const($db, "MAIN_SECURITY_HASH_ALGO", 'password_hash', 'chaine', 0, '', 0); // All entities
						} else {
							dolibarr_set_const($db, "MAIN_SECURITY_HASH_ALGO", 'sha1md5', 'chaine', 0, '', 0); // All entities
						}
					}

					error_log('step5: DATABASE_PWD_ENCRYPTED = ' . $conf->global->DATABASE_PWD_ENCRYPTED . ' MAIN_SECURITY_HASH_ALGO = ' . $conf->global->MAIN_SECURITY_HASH_ALGO, LOG_INFO);
				}

				// Create user used to create the admin user
				$createuser = new User($db);
				$createuser->id = 0;
				$createuser->admin = 1;

				// Set admin user
				$newuser = new User($db);
				$newuser->lastname = 'SuperAdmin';
				$newuser->firstname = '';
				$newuser->login = $login;
				$newuser->pass = $pass;
				$newuser->admin = 1;
				$newuser->entity = 0;

				$conf->global->USER_MAIL_REQUIRED = 0; // Force global option to be sure to create a new user with no email
				$conf->global->USER_PASSWORD_GENERATED = ''; // To not use any rule for password validation

				$result = $newuser->create($createuser, 1);
				if ($result > 0) {
					error_log($langs->trans("AdminLoginCreatedSuccessfuly", $login));
					$success = 1;
				} else {
					if ($result == -6) { //login or email already exists
						error_log('step5: AdminLoginAlreadyExists', LOG_WARNING);
						error_log($newuser->error);
						$success = 1;
					} else {
						error_log('step5: FailedToCreateAdminLogin ' . $newuser->error, LOG_ERR);
						setEventMessages($langs->trans("FailedToCreateAdminLogin") . ' ' . $newuser->error, null, 'errors');
						$error_msg = $error_msg . $langs->trans("FailedToCreateAdminLogin") . ' ' . $newuser->error;
						error_log($error_msg);
						return json_encode(
							array(
								"IsSuccess" => false,
								"message" => $error_msg,
							),
							JSON_UNESCAPED_UNICODE
						);
					}
				}

				if ($success) {
					// Insert MAIN_VERSION_FIRST_INSTALL in a dedicated transaction. So if it fails (when first install was already done), we can do other following requests.
					$db->begin();
					error_log('step5: set MAIN_VERSION_FIRST_INSTALL const to ' . $targetversion, LOG_DEBUG);
					$resql = $db->query("INSERT INTO " . MAIN_DB_PREFIX . "const(name, value, type, visible, note, entity) values(" . $db->encrypt('MAIN_VERSION_FIRST_INSTALL') . ", " . $db->encrypt($targetversion) . ", 'chaine', 0, 'Dolibarr version when first install', 0)");
					if ($resql) {
						$conf->global->MAIN_VERSION_FIRST_INSTALL = $targetversion;
						$db->commit();
					} else {
						//if (! $resql) dol_print_error($db,'Error in setup program');      // We ignore errors. Key may already exists
						$db->commit();
					}

					$db->begin();

					error_log('step5: set MAIN_VERSION_LAST_INSTALL const to ' . $targetversion, LOG_DEBUG);
					$resql = $db->query("DELETE FROM " . MAIN_DB_PREFIX . "const WHERE " . $db->decrypt('name') . " = 'MAIN_VERSION_LAST_INSTALL'");
					if (!$resql) {
						$error_msg = $error_msg . 'Error in setup program';
						error_log($error_msg);
						return json_encode(
							array(
								"IsSuccess" => false,
								"message" => $error_msg,
							),
							JSON_UNESCAPED_UNICODE
						);
					}
					$resql = $db->query("INSERT INTO " . MAIN_DB_PREFIX . "const(name,value,type,visible,note,entity) values(" . $db->encrypt('MAIN_VERSION_LAST_INSTALL') . ", " . $db->encrypt($targetversion) . ", 'chaine', 0, 'Dolibarr version when last install', 0)");
					if (!$resql) {
						$error_msg = $error_msg . 'Error in setup program';
						error_log($error_msg);
						return json_encode(
							array(
								"IsSuccess" => false,
								"message" => $error_msg,
							),
							JSON_UNESCAPED_UNICODE
						);
					}
					$conf->global->MAIN_VERSION_LAST_INSTALL = $targetversion;

					if ($useforcedwizard) {
						error_log('step5: set MAIN_REMOVE_INSTALL_WARNING const to 1', LOG_DEBUG);
						$resql = $db->query("DELETE FROM " . MAIN_DB_PREFIX . "const WHERE " . $db->decrypt('name') . " = 'MAIN_REMOVE_INSTALL_WARNING'");
						if (!$resql) {
							$error_msg = $error_msg . 'Error in setup program';
							error_log($error_msg);
							return json_encode(
								array(
									"IsSuccess" => false,
									"message" => $error_msg,
								),
								JSON_UNESCAPED_UNICODE
							);
						}
					}

					// List of modules to enable
					$tmparray = array();

					// If we ask to force some modules to be enabled
					if (!empty($force_install_module)) {
						if (!defined('DOL_DOCUMENT_ROOT') && !empty($dolibarr_main_document_root)) {
							define('DOL_DOCUMENT_ROOT', $dolibarr_main_document_root);
						}

						$tmparray = explode(',', $force_install_module);
					}

					$modNameLoaded = array();

					// Search modules dirs
					$modulesdir[] = $dolibarr_main_document_root . '/core/modules/';

					foreach ($modulesdir as $dir) {
						// Load modules attributes in arrays (name, numero, orders) from dir directory
						//print $dir."\n<br>";
						error_log("Scan directory " . $dir . " for module descriptor files (modXXX.class.php)");

						$handle = @opendir($dir);
						if (is_resource($handle)) {
							while (($file = readdir($handle)) !== false) {
								if (is_readable($dir . $file) && substr($file, 0, 3) == 'mod' && substr($file, dol_strlen($file) - 10) == '.class.php') {
									$modName = substr($file, 0, dol_strlen($file) - 10);
									if ($modName) {
										if (!empty($modNameLoaded[$modName])) { // In cache of already loaded modules ?
											$mesg = "Error: Module " . $modName . " was found twice: Into " . $modNameLoaded[$modName] . " and " . $dir . ". You probably have an old file on your disk.<br>";
											setEventMessages($mesg, null, 'warnings');
											error_log($mesg, LOG_ERR);
											continue;
										}

										try {
											$res = include_once $dir . $file; // A class already exists in a different file will send a non catchable fatal error.
											if (class_exists($modName)) {
												$objMod = new $modName($db);
												$modNameLoaded[$modName] = $dir;
												if (!empty($objMod->enabled_bydefault) && !in_array($file, $tmparray)) {
													$tmparray[] = $file;
												}
											}
										} catch (Exception $e) {
											error_log("Failed to load " . $dir . $file . " " . $e->getMessage(), LOG_ERR);
										}
									}
								}
							}
						}
					}

					// Loop on each modules to activate it
					if (!empty($tmparray)) {
						foreach ($tmparray as $modtoactivate) {
							$modtoactivatenew = preg_replace('/\.class\.php$/i', '', $modtoactivate);
							//print $langs->trans("ActivateModule", $modtoactivatenew).'<br>';

							$file = $modtoactivatenew . '.class.php';
							error_log('step5: activate module file=' . $file);
							$res = dol_include_once("/core/modules/" . $file);

							$res = activateModule($modtoactivatenew, 1);
							if (!empty($res['errors'])) {
								error_log('ERROR: failed to activateModule() file=' . $file);
							}
						}
						//print '<br>';
					}

					// Now delete the flag to say install is complete
					error_log('step5: remove MAIN_NOT_INSTALLED const');
					$resql = $db->query("DELETE FROM " . MAIN_DB_PREFIX . "const WHERE " . $db->decrypt('name') . " = 'MAIN_NOT_INSTALLED'");
					if (!$resql) {
						$error_msg = $error_msg . 'Error in setup program';
						error_log($error_msg);
						return json_encode(
							array(
								"IsSuccess" => false,
								"message" => $error_msg,
							),
							JSON_UNESCAPED_UNICODE
						);
					}

					$db->commit();
				}
			} else {
				print $langs->trans("ErrorFailedToConnect") . "<br>";
			}
		} elseif (empty($action) || preg_match('/upgrade/i', $action)) {
			// If upgrade
			if ($db->connected) {
				$conf->setValues($db);
				// Reset forced setup after the setValues
				if (defined('SYSLOG_FILE')) {
					$conf->global->SYSLOG_FILE = constant('SYSLOG_FILE');
				}
				$conf->global->MAIN_ENABLE_LOG_TO_HTML = 1;

				// Define if we need to update the MAIN_VERSION_LAST_UPGRADE value in database
				$tagdatabase = false;
				if (empty($conf->global->MAIN_VERSION_LAST_UPGRADE)) {
					$tagdatabase = true; // We don't know what it was before, so now we consider we are version choosed.
				} else {
					$mainversionlastupgradearray = preg_split('/[.-]/', $conf->global->MAIN_VERSION_LAST_UPGRADE);
					$targetversionarray = preg_split('/[.-]/', $targetversion);
					if (versioncompare($targetversionarray, $mainversionlastupgradearray) > 0) {
						$tagdatabase = true;
					}
				}

				if ($tagdatabase) {
					error_log('step5: set MAIN_VERSION_LAST_UPGRADE const to value ' . $targetversion);
					$resql = $db->query("DELETE FROM " . MAIN_DB_PREFIX . "const WHERE " . $db->decrypt('name') . " = 'MAIN_VERSION_LAST_UPGRADE'");
					if (!$resql) {
						$error_msg = $error_msg . 'Error in setup program';
						error_log($error_msg);
						return json_encode(
							array(
								"IsSuccess" => false,
								"message" => $error_msg,
							),
							JSON_UNESCAPED_UNICODE
						);
					}
					$resql = $db->query("INSERT INTO " . MAIN_DB_PREFIX . "const(name, value, type, visible, note, entity) VALUES (" . $db->encrypt('MAIN_VERSION_LAST_UPGRADE') . ", " . $db->encrypt($targetversion) . ", 'chaine', 0, 'Dolibarr version for last upgrade', 0)");
					if (!$resql) {
						$error_msg = $error_msg . 'Error in setup program';
						error_log($error_msg);
						return json_encode(
							array(
								"IsSuccess" => false,
								"message" => $error_msg,
							),
							JSON_UNESCAPED_UNICODE
						);
					}
					$conf->global->MAIN_VERSION_LAST_UPGRADE = $targetversion;
				} else {
					error_log('step5: we run an upgrade to version ' . $targetversion . ' but database was already upgraded to ' . $conf->global->MAIN_VERSION_LAST_UPGRADE . '. We keep MAIN_VERSION_LAST_UPGRADE as it is.');
				}
			} else {
				error_log($langs->trans("ErrorFailedToConnect"));
			}
		} else {
			$error_msg = $error_msg . 'step5.php: unknown choice of action';
			error_log($error_msg);
			return json_encode(
				array(
					"IsSuccess" => false,
					"message" => $error_msg,
				),
				JSON_UNESCAPED_UNICODE
			);
		}

		// May fail if parameter already defined
		$resql = $db->query("INSERT INTO " . MAIN_DB_PREFIX . "const(name,value,type,visible,note,entity) VALUES (" . $db->encrypt('MAIN_LANG_DEFAULT') . ", " . $db->encrypt($setuplang) . ", 'chaine', 0, 'Default language', 1)");
		//if (! $resql) dol_print_error($db,'Error in setup program');

		$db->close();
	}



	// Create lock file

	// If first install
	if ($action == "set") {
		if ($success) {
			if (empty($conf->global->MAIN_VERSION_LAST_UPGRADE) || ($conf->global->MAIN_VERSION_LAST_UPGRADE == DOL_VERSION)) {
				// Install is finished (database is on same version than files)
				error_log($langs->trans("SystemIsInstalled"));

				// Create install.lock file
				// No need for the moment to create it automatically, creation by web assistant means permissions are given
				// to the web user, it is better to show a warning to say to create it manually with correct user/permission (not erasable by a web process)
				$createlock = 0;
				if (!empty($force_install_lockinstall) || !empty($conf->global->MAIN_ALWAYS_CREATE_LOCK_AFTER_LAST_UPGRADE)) {
					// Install is finished, we create the "install.lock" file, so install won't be possible anymore.
					// TODO Upgrade will be still be possible if a file "upgrade.unlock" is present
					$lockfile = DOL_DATA_ROOT . '/install.lock';
					$fp = @fopen($lockfile, "w");
					if ($fp) {
						if (empty($force_install_lockinstall) || $force_install_lockinstall == 1) {
							$force_install_lockinstall = '444'; // For backward compatibility
						}
						fwrite($fp, "This is a lock file to prevent use of install or upgrade pages (set with permission " . $force_install_lockinstall . ")");
						fclose($fp);
						dolChmod($lockfile, $force_install_lockinstall);

						$createlock = 1;
					}
				}
				if (empty($createlock)) {
					error_log($langs->trans("WarningRemoveInstallDir"));
				}

				error_log($langs->trans("YouNeedToPersonalizeSetup") . (isset($login) ? '&username=' . urlencode($login) : '') . '">' . $langs->trans("GoToSetupArea"));
			} else {
				// If here MAIN_VERSION_LAST_UPGRADE is not empty
				error_log($langs->trans("VersionLastUpgrade") . ':' . $conf->global->MAIN_VERSION_LAST_UPGRADE . ',' . $langs->trans("VersionProgram") . ':' . DOL_VERSION . ',' . $langs->trans("MigrationNotFinished"), ',' . $dolibarr_main_url_root . ',' . $langs->trans("GoToUpgradePage"));
			}
		}
	} elseif (empty($action) || preg_match('/upgrade/i', $action)) {
		// If upgrade
		if (empty($conf->global->MAIN_VERSION_LAST_UPGRADE) || ($conf->global->MAIN_VERSION_LAST_UPGRADE == DOL_VERSION)) {
			// Upgrade is finished (database is on the same version than files)
			error_log($langs->trans("SystemIsUpgraded"));

			// Create install.lock file if it does not exists.
			// Note: it should always exists. A better solution to allow upgrade will be to add an upgrade.unlock file
			$createlock = 0;
			if (!empty($force_install_lockinstall) || !empty($conf->global->MAIN_ALWAYS_CREATE_LOCK_AFTER_LAST_UPGRADE)) {
				// Upgrade is finished, we modify the lock file
				$lockfile = DOL_DATA_ROOT . '/install.lock';
				$fp = @fopen($lockfile, "w");
				if ($fp) {
					if (empty($force_install_lockinstall) || $force_install_lockinstall == 1) {
						$force_install_lockinstall = '444'; // For backward compatibility
					}
					fwrite($fp, "This is a lock file to prevent use of install or upgrade pages (set with permission " . $force_install_lockinstall . ")");
					fclose($fp);
					dolChmod($lockfile, $force_install_lockinstall);

					$createlock = 1;
				}
			}
			if (empty($createlock)) {
				error_log($langs->trans("WarningRemoveInstallDir"));
			}

			// Delete the upgrade.unlock file it it exists
			$unlockupgradefile = DOL_DATA_ROOT . '/upgrade.unlock';
			dol_delete_file($unlockupgradefile, 0, 0, 0, null, false, 0);

			$morehtml = '<br><div class="center"><a href="../index.php?mainmenu=home' . (isset($login) ? '&username=' . urlencode($login) : '') . '">';
			$morehtml .= '<span class="fas fa-link-alt"></span> ' . $langs->trans("GoToDolibarr") . '...';
			$morehtml .= '</a></div><br>';
		} else {
			// If here MAIN_VERSION_LAST_UPGRADE is not empty
			error_log($langs->trans("VersionLastUpgrade") . ':' . $conf->global->MAIN_VERSION_LAST_UPGRADE);
			error_log($langs->trans("VersionProgram") . ': ' . DOL_VERSION);


			$morehtml = '<br><div class="center"><a href="../install/index.php">';
			$morehtml .= '<span class="fas fa-link-alt"></span> ' . $langs->trans("GoToUpgradePage");
			$morehtml .= '</a></div>';
		}
	} else {
		$error_msg = $error_msg . 'step5.php: unknown choice of action=' . $action . ' in create lock file seaction';
		error_log($error_msg);
		return json_encode(
			array(
				"IsSuccess" => false,
				"message" => $error_msg,
			),
			JSON_UNESCAPED_UNICODE
		);
	}

	// Clear cache files
	clearstatcache();

	$ret = 0;
	if ($error && isset($argv[1])) {
		$ret = 1;
	}
	error_log("Exit " . $ret);

	error_log("- step5: Dolibarr setup finished");


	// Return code if ran from command line
	if ($ret) {
		exit($ret);
	}
	return $error;
}