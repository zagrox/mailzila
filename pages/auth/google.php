<?php
require_once __DIR__ . '/../../includes/header.php';

$auth = new Auth();
$error = null;

// Google OAuth configuration
$clientId = $_ENV['GOOGLE_CLIENT_ID'];
$clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'];
$redirectUri = $_ENV['APP_URL'] . '/auth/google';

// If we have a code, exchange it for tokens
if (isset($_GET['code'])) {
    try {
        // Exchange code for tokens
        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'code' => $_GET['code'],
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code'
        ]));
        
        $response = curl_exec($ch);
        $tokens = json_decode($response, true);
        curl_close($ch);

        if (isset($tokens['access_token'])) {
            // Get user info
            $ch = curl_init('https://www.googleapis.com/oauth2/v2/userinfo');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $tokens['access_token']
            ]);
            
            $response = curl_exec($ch);
            $userInfo = json_decode($response, true);
            curl_close($ch);

            // Prepare user data for social login
            $userData = [
                'id' => $userInfo['id'],
                'email' => $userInfo['email'],
                'firstName' => $userInfo['given_name'],
                'lastName' => $userInfo['family_name'],
                'avatar' => $userInfo['picture'],
                'accessToken' => $tokens['access_token'],
                'refreshToken' => $tokens['refresh_token'] ?? null,
                'expiresAt' => date('Y-m-d H:i:s', time() + $tokens['expires_in'])
            ];

            // Perform social login
            if ($auth->socialLogin('google', $userData)) {
                header('Location: /');
                exit;
            } else {
                $error = 'Failed to complete Google login.';
            }
        } else {
            $error = 'Failed to get access token from Google.';
        }
    } catch (Exception $e) {
        $error = 'An error occurred during Google login.';
        if (APP_DEBUG) {
            error_log($e->getMessage());
        }
    }
} else {
    // Redirect to Google OAuth consent screen
    $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
        'client_id' => $clientId,
        'redirect_uri' => $redirectUri,
        'response_type' => 'code',
        'scope' => 'email profile',
        'access_type' => 'offline',
        'prompt' => 'consent'
    ]);
    
    header('Location: ' . $authUrl);
    exit;
}
?>

<?php if ($error): ?>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="alert alert-danger mt-5">
                    <?php echo htmlspecialchars($error); ?>
                </div>
                <div class="text-center">
                    <a href="/auth/login" class="btn btn-primary">Back to Login</a>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/../../includes/footer.php'; ?> 