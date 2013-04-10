<?php session_start();

require 'PasswordHash.php';
require 'pwqcheck.php';

require('db/config.php');

// In a real application, these should be in a config file instead
$db_host = $host;
$db_user = $username;
$db_pass = $password;
$db_name = $db;

// Do we have the pwqcheck(1) program from the passwdqc package?
$use_pwqcheck = FALSE;
// We can override the default password policy
$pwqcheck_args = '';
#$pwqcheck_args = 'config=/etc/passwdqc.conf';

// Base-2 logarithm of the iteration count used for password stretching
$hash_cost_log2 = 8;
// Do we require the hashes to be portable to older systems (less secure)?
$hash_portable = FALSE;

/* Dummy salt to waste CPU time on when a non-existent username is requested.
 * This should use the same hash type and cost parameter as we're using for
 * real/new hashes.  The intent is to mitigate timing attacks (probing for
 * valid usernames).  This is optional - the line may be commented out if you
 * don't care about timing attacks enough to spend CPU time on mitigating them
 * or if you can't easily determine what salt string would be appropriate. */
$dummy_salt = '$2a$08$1234567890123456789012';

// Are we debugging this code?  If enabled, OK to leak server setup details.
$debug = FALSE;

function fail($pub, $pvt = '')
{
	global $debug;
	$msg = $pub;
	if ($debug && $pvt !== '')
		$msg .= ": $pvt";
/* The $pvt debugging messages may contain characters that would need to be
 * quoted if we were producing HTML output, like we would be in a real app,
 * but we're using text/plain here.  Also, $debug is meant to be disabled on
 * a "production install" to avoid leaking server setup details. */
	return "An error occurred ($msg).\n";
}

function my_pwqcheck($newpass, $oldpass = '', $user = '')
{
	
    global $use_pwqcheck, $pwqcheck_args;
	if ($use_pwqcheck)
		return pwqcheck($newpass, $oldpass, $user, '', $pwqcheck_args);

    //Some really trivial and obviously-insufficient password strength checks - we ought to use the pwqcheck(1) program instead.
	$check = '';
	if (strlen($newpass) < 7)
		$check = 'way too short';
	else if (stristr($oldpass, $newpass) ||
	    (strlen($oldpass) >= 4 && stristr($newpass, $oldpass)))
		$check = 'is based on the old one';
	else if (stristr($user, $newpass) ||
	    (strlen($user) >= 4 && stristr($newpass, $user)))
		$check = 'is based on the username';
	if ($check)
		return "Bad password ($check)";
      
	return 'OK';
}

function get_post_var($var)
{
	$val = $_POST[$var];
	if (get_magic_quotes_gpc())
		$val = stripslashes($val);
	return $val;
}

function manage_user() {
    require('db/config.php');

    // In a real application, these should be in a config file instead
    $db_host = $host;
    $db_user = $username;
    $db_pass = $password;
    $db_name = $db;

    $op = $_POST['op'];
    if ($op !== 'new' && $op !== 'login' && $op !== 'change')
        return fail('Unknown request');

    $user = get_post_var('user');
    /* Sanity-check the username, don't rely on our use of prepared statements
     * alone to prevent attacks on the SQL server via malicious usernames. */
    if (!preg_match('/^[a-zA-Z0-9_]{1,60}$/', $user))
        return fail('Invalid username');

    $pass = get_post_var('pass');
    /* Don't let them spend more of our CPU time than we were willing to.
     * Besides, bcrypt happens to use the first 72 characters only anyway. */
    if (strlen($pass) > 72)
        return fail('The supplied password is too long');

    $db = new mysqli($db_host, $db_user, $db_pass, $db_name);
    if (mysqli_connect_errno())
        return fail('MySQL connect', mysqli_connect_error());

    $hasher = new PasswordHash($hash_cost_log2, $hash_portable);

    if ($op === 'new') {
        if (($check = my_pwqcheck($pass, '', $user)) !== 'OK')
            return fail($check);

        $hash = $hasher->HashPassword($pass);
        if (strlen($hash) < 20)
            return fail('return failed to hash new password');
        unset($hasher);

        if(!($stmt = $db->prepare('INSERT INTO Users (user, pass) VALUES (?, ?)'))) {
            return fail('MySQL prepare', $db->error);
        }
        else {
            if(!($stmt->bind_param('ss', $user, $hash))) {
                return fail('MySQL bind_param', $db->error);
            }
            else {
                if (!$stmt->execute()) {
                    if ($db->errno === 1062 /* ER_DUP_ENTRY */)
                        return fail('This username is already taken');
                    else
                        return fail('MySQL execute', $db->error);
                }
            }
        }

        $what = 'User created';
        $_SESSION['user'] = $user;
    } else {
        $hash = '*'; // In case the user is not found
        if(!($stmt = $db->prepare('SELECT pass FROM Users WHERE user=?')))
            return fail('MySQL prepare', $db->error);
        if(!($stmt->bind_param('s', $user)))
            return fail('MySQL bind_param', $db->error);
        if(!($stmt->execute()))
            return fail('MySQL execute', $db->error);
        if(!($stmt->bind_result($hash)))
            return fail('MySQL bind_result', $db->error);
        if (!$stmt->fetch() && $db->errno)
            return fail('MySQL fetch', $db->error);

    // Mitigate timing attacks (probing for valid usernames)
        if (isset($dummy_salt) && strlen($hash) < 20)
            $hash = $dummy_salt;

        if ($hasher->CheckPassword($pass, $hash)) {
            $what = 'Authentication succeeded';
            $_SESSION['user'] = $user;
        } else {
            $what = 'Authentication failed';
            $op = 'fail'; // Definitely not 'change'
        }

        if ($op === 'change') {
            $stmt->close();

            $newpass = get_post_var('newpass');
            if (strlen($newpass) > 72)
                return fail('The new password is too long');
            if (($check = my_pwqcheck($newpass, $pass, $user)) !== 'OK')
                return fail($check);
            $hash = $hasher->HashPassword($newpass);
            if (strlen($hash) < 20)
                return fail('Failed to hash new password');
            unset($hasher);

            if(!($stmt = $db->prepare('UPDATE Users SET pass=? WHERE user=?')))
                return fail('MySQL prepare', $db->error);
            if(!($stmt->bind_param('ss', $hash, $user)))
                return fail('MySQL bind_param', $db->error);
            if(!($stmt->execute()))
                return fail('MySQL execute', $db->error);

            $what = 'Password changed';
        }

        unset($hasher);
    }

    $stmt->close();
    $db->close();

    return $what;
}
?>