<?php
require_once __DIR__ . '/../../includes/header.php';

$auth = new Auth();
$error = null;

// GitHub OAuth configuration
$clientId = $_ENV['GITHUB_CLIENT_ID'];
$clientSecret = $_ENV['GITHUB_CLIENT_SECRET'];
$redirectUri = $_ENV['APP_URL'] . '/auth/github';

// If we have a code, exchange it for tokens
if (isset($_GET['code'])) {
    try {
        // Exchange code for tokens
        $ch = curl_init('https://github.com/login/oauth/access_token');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'code' => $_GET['code'],
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json'
        ]);
        
        $response = curl_exec($ch);
        $tokens = json_decode($response, true);
        curl_close($ch);

        if (isset($tokens['access_token'])) {
            // Get user info
            $ch = curl_init('https://api.github.com/user');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: token ' . $tokens['access_token'],
                'Accept: application/json'
            ]);
            
            $response = curl_exec($ch);
            $userInfo = json_decode($response, true);
            curl_close($ch);

            // Get user email
            $ch = curl_init('https://api.github.com/user/emails');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: token ' . $tokens['access_token'],
                'Accept: application/json'
            ]);
            
            $response = curl_exec($ch);
            $emails = json_decode($response, true);
            curl_close($ch);

            // Find primary email
            $primaryEmail = null;
            foreach ($emails as $email) {
                if ($email['primary']) {
                    $primaryEmail = $email['email'];
                    break;
                }
            }

            if (!$primaryEmail) {
                throw new Exception('No primary email found');
            }

            // Prepare user data for social login
            $userData = [
                'id' => $userInfo['id'],
                'email' => $primaryEmail,
                'firstName' => $userInfo['name'] ?? explode(' ', $userInfo['login'])[0],
                'lastName' => count(explode(' ', $userInfo['name'] ?? $userInfo['login'])) > 1 ? end(explode(' ', $userInfo['name'])) : '',
                'avatar' => $userInfo['avatar_url'],
                'accessToken' => $tokens['access_token'],
                'refreshToken' => null, // GitHub doesn't provide refresh tokens
                'expiresAt' => null // GitHub tokens don't expire
            ];

            // Perform social login
            if ($auth->socialLogin('github', $userData)) {
                header('Location: /');
                exit;
            } else {
                $error = 'Failed to complete GitHub login.';
            }
        } else {
            $error = 'Failed to get access token from GitHub.';
        }
    } catch (Exception $e) {
        $error = 'An error occurred during GitHub login.';
        if (APP_DEBUG) {
            error_log($e->getMessage());
        }
    }
} else {
    // Redirect to GitHub OAuth consent screen
    $authUrl = 'https://github.com/login/oauth/authorize?' . http_build_query([
        'client_id' => $clientId,
        'redirect_uri' => $redirectUri,
        'scope' => 'user:email'
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