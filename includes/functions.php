<?php
// ============================================================
//  includes/functions.php — Global Utility Functions
// ============================================================

/**
 * Sanitize output to prevent XSS.
 */
function h(string $str): string {
    return htmlspecialchars($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

/**
 * Redirect to a URL and exit.
 */
function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

/**
 * Flash message system — store a message in session.
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Retrieve and clear flash message.
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Render Bootstrap alert from a flash message.
 */
function renderFlash(): void {
    $flash = getFlash();
    if (!$flash) return;
    $map = [
        'success' => 'success',
        'error'   => 'danger',
        'warning' => 'warning',
        'info'    => 'info',
    ];
    $cls = $map[$flash['type']] ?? 'info';
    echo '<div class="alert alert-' . $cls . ' alert-dismissible fade show" role="alert">'
        . h($flash['message'])
        . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>'
        . '</div>';
}

/**
 * Secure image upload.
 * Returns the saved filename on success, or throws RuntimeException on failure.
 */
function uploadImage(array $file, string $destDir): string {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Erreur lors du téléchargement du fichier.');
    }
    if ($file['size'] > MAX_FILE_SIZE) {
        throw new RuntimeException('Le fichier dépasse la taille maximale autorisée (5 Mo).');
    }

    // Validate MIME type via finfo
    $finfo    = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    if (!in_array($mimeType, ALLOWED_TYPES, true)) {
        throw new RuntimeException('Type de fichier non autorisé. Utilisez JPG, PNG, WEBP ou GIF.');
    }

    // Validate extension
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXT, true)) {
        throw new RuntimeException('Extension non autorisée.');
    }

    // Generate unique filename
    $filename = uniqid('img_', true) . '.' . $ext;
    $destPath = rtrim($destDir, '/') . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destPath)) {
        throw new RuntimeException('Impossible de sauvegarder le fichier.');
    }

    return $filename;
}

/**
 * Delete a file from uploads if it exists and is not a default.
 */
function deleteUpload(string $filename, string $dir): void {
    if (strpos($filename, 'default') === false) {
        $path = rtrim($dir, '/') . '/' . $filename;
        if (file_exists($path)) {
            unlink($path);
        }
    }
}

/**
 * Format a MySQL datetime to a human-readable relative time.
 */
function timeAgo(string $datetime): string {
    $now  = new DateTime();
    $past = new DateTime($datetime);
    $diff = $now->diff($past);

    if ($diff->y > 0)  return 'il y a ' . $diff->y . ' an' . ($diff->y > 1 ? 's' : '');
    if ($diff->m > 0)  return 'il y a ' . $diff->m . ' mois';
    if ($diff->d > 0)  return 'il y a ' . $diff->d . ' jour' . ($diff->d > 1 ? 's' : '');
    if ($diff->h > 0)  return 'il y a ' . $diff->h . 'h';
    if ($diff->i > 0)  return 'il y a ' . $diff->i . ' min';
    return 'à l\'instant';
}

/**
 * Truncate text to a given length.
 */
function truncate(string $text, int $limit = 100): string {
    if (mb_strlen($text) <= $limit) return $text;
    return mb_substr($text, 0, $limit) . '…';
}

/**
 * Generate a CSRF token and store in session.
 */
function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Validate a submitted CSRF token.
 */
function verifyCsrf(string $token): bool {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

/**
 * Render a hidden CSRF input field.
 */
function csrfField(): string {
    return '<input type="hidden" name="csrf_token" value="' . h(csrfToken()) . '">';
}

/**
 * Return avatar URL for a user array.
 */
function avatarUrl(array $user): string {
    if (!empty($user['avatar']) && file_exists(UPLOAD_AVATARS . $user['avatar'])) {
        return UPLOAD_URL_AVATARS . h($user['avatar']);
    }
    return SITE_URL . '/assets/images/default_avatar.jpg';
}

/**
 * Return avatar URL from a raw avatar filename.
 */
function avatarUrlFromFilename(?string $filename): string {
    if (!empty($filename) && file_exists(UPLOAD_AVATARS . $filename)) {
        return UPLOAD_URL_AVATARS . h($filename);
    }
    return SITE_URL . '/assets/images/default_avatar.jpg';
}

/**
 * Return post image URL.
 */
function postImageUrl(array $post): string {
    if (!empty($post['image']) && file_exists(UPLOAD_POSTS . $post['image'])) {
        return UPLOAD_URL_POSTS . h($post['image']);
    }
    return SITE_URL . '/assets/images/default_post.png';
}

/**
 * Return badge HTML for a post category.
 */
function categoryBadge(string $category): string {
    $colors = [
        'robe'          => 'badge-pink',
        'costume'       => 'badge-purple',
        'accessoire'    => 'badge-gold',
        'streetwear'    => 'badge-dark',
        'haute_couture' => 'badge-rose',
        'autre'         => 'badge-secondary',
    ];
    $cls   = $colors[$category] ?? 'badge-secondary';
    $label = ucfirst(str_replace('_', ' ', $category));
    return '<span class="badge ' . $cls . '">' . h($label) . '</span>';
}

/**
 * Send password reset email using configured transport.
 */
function sendPasswordResetEmail(string $toEmail, string $toName, string $resetLink): bool {
    $subject = 'Reinitialisation de votre mot de passe';
    $safeName = $toName !== '' ? h($toName) : 'utilisateur';
    $safeLink = h($resetLink);
    $html = '<p>Bonjour ' . $safeName . ',</p>'
        . '<p>Vous avez demande la reinitialisation de votre mot de passe.</p>'
        . '<p><a href="' . $safeLink . '">Cliquez ici pour definir un nouveau mot de passe</a></p>'
        . '<p>Si vous n etes pas a l origine de cette demande, ignorez cet email.</p>';
    $text = "Bonjour {$toName},\n\n"
        . "Vous avez demande la reinitialisation de votre mot de passe.\n"
        . "Utilisez ce lien : {$resetLink}\n\n"
        . "Si vous n etes pas a l origine de cette demande, ignorez cet email.";

    return sendAppEmail($toEmail, $toName, $subject, $html, $text);
}

/**
 * Send a generic email.
 */
function sendAppEmail(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody = ''): bool {
    $transport = strtolower(MAIL_TRANSPORT);

    if ($transport === 'smtp') {
        return sendSmtpEmail($toEmail, $toName, $subject, $htmlBody, $textBody);
    }

    return sendMailFunctionEmail($toEmail, $toName, $subject, $htmlBody, $textBody);
}

function sendMailFunctionEmail(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody = ''): bool {
    $boundary = 'bnd_' . bin2hex(random_bytes(12));
    $headers = [
        'MIME-Version: 1.0',
        'From: ' . formatEmailAddress(MAIL_FROM_EMAIL, MAIL_FROM_NAME),
        'Reply-To: ' . MAIL_FROM_EMAIL,
        'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
    ];

    $body = "--{$boundary}\r\n"
        . "Content-Type: text/plain; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: 8bit\r\n\r\n"
        . ($textBody !== '' ? $textBody : strip_tags($htmlBody)) . "\r\n"
        . "--{$boundary}\r\n"
        . "Content-Type: text/html; charset=UTF-8\r\n"
        . "Content-Transfer-Encoding: 8bit\r\n\r\n"
        . $htmlBody . "\r\n"
        . "--{$boundary}--\r\n";

    return @mail(
        formatEmailAddress($toEmail, $toName),
        encodeEmailHeader($subject),
        $body,
        implode("\r\n", $headers)
    );
}

function sendSmtpEmail(string $toEmail, string $toName, string $subject, string $htmlBody, string $textBody = ''): bool {
    if (SMTP_HOST === '') {
        return false;
    }

    $scheme = strtolower(SMTP_ENCRYPTION) === 'ssl' ? 'ssl://' : '';
    $socket = @stream_socket_client(
        $scheme . SMTP_HOST . ':' . SMTP_PORT,
        $errno,
        $errstr,
        SMTP_TIMEOUT
    );

    if (!$socket) {
        return false;
    }

    stream_set_timeout($socket, SMTP_TIMEOUT);

    try {
        smtpExpect($socket, [220]);
        smtpCommand($socket, 'EHLO localhost', [250]);

        if (strtolower(SMTP_ENCRYPTION) === 'tls') {
            smtpCommand($socket, 'STARTTLS', [220]);
            if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                throw new RuntimeException('TLS failed');
            }
            smtpCommand($socket, 'EHLO localhost', [250]);
        }

        if (SMTP_USERNAME !== '') {
            smtpCommand($socket, 'AUTH LOGIN', [334]);
            smtpCommand($socket, base64_encode(SMTP_USERNAME), [334]);
            smtpCommand($socket, base64_encode(SMTP_PASSWORD), [235]);
        }

        smtpCommand($socket, 'MAIL FROM:<' . MAIL_FROM_EMAIL . '>', [250]);
        smtpCommand($socket, 'RCPT TO:<' . $toEmail . '>', [250, 251]);
        smtpCommand($socket, 'DATA', [354]);

        $boundary = 'bnd_' . bin2hex(random_bytes(12));
        $headers = [
            'From: ' . formatEmailAddress(MAIL_FROM_EMAIL, MAIL_FROM_NAME),
            'To: ' . formatEmailAddress($toEmail, $toName),
            'Subject: ' . encodeEmailHeader($subject),
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="' . $boundary . '"',
        ];

        $message = implode("\r\n", $headers) . "\r\n\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: text/plain; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n"
            . ($textBody !== '' ? $textBody : strip_tags($htmlBody)) . "\r\n"
            . "--{$boundary}\r\n"
            . "Content-Type: text/html; charset=UTF-8\r\n"
            . "Content-Transfer-Encoding: 8bit\r\n\r\n"
            . $htmlBody . "\r\n"
            . "--{$boundary}--\r\n.";

        fwrite($socket, preg_replace("/(?m)^\\./", '..', $message) . "\r\n");
        smtpExpect($socket, [250]);
        smtpCommand($socket, 'QUIT', [221]);
        fclose($socket);
        return true;
    } catch (Throwable $e) {
        fclose($socket);
        return false;
    }
}

function smtpCommand($socket, string $command, array $okCodes): void {
    fwrite($socket, $command . "\r\n");
    smtpExpect($socket, $okCodes);
}

function smtpExpect($socket, array $okCodes): void {
    $response = '';
    while (($line = fgets($socket, 515)) !== false) {
        $response .= $line;
        if (strlen($line) >= 4 && $line[3] === ' ') {
            break;
        }
    }

    $code = (int)substr($response, 0, 3);
    if (!in_array($code, $okCodes, true)) {
        throw new RuntimeException('SMTP error: ' . trim($response));
    }
}

function formatEmailAddress(string $email, string $name = ''): string {
    if ($name === '') {
        return $email;
    }
    return encodeEmailHeader($name) . ' <' . $email . '>';
}

function encodeEmailHeader(string $value): string {
    return '=?UTF-8?B?' . base64_encode($value) . '?=';
}
