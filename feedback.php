<?php
// Database configuration - adjust if needed
$dbHost = "localhost";
$dbName = "feedback_app";
$dbUser = "root";
$dbPass = "";

// Initialize variables
$name = '';
$email = '';
$message = '';
$errors = [];
$success = false;

// Create PDO connection
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . htmlspecialchars($e->getMessage()));
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validate inputs
    if ($name === '') {
        $errors['name'] = "Please enter your name.";
    } elseif (mb_strlen($name) > 100) {
        $errors['name'] = "Name must be less than 100 characters.";
    }

    if ($email === '') {
        $errors['email'] = "Please enter your email.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = "Please enter a valid email address.";
    } elseif (mb_strlen($email) > 100) {
        $errors['email'] = "Email must be less than 100 characters.";
    }

    if ($message === '') {
        $errors['message'] = "Please enter your feedback.";
    } elseif (mb_strlen($message) > 1000) {
        $errors['message'] = "Feedback must be less than 1000 characters.";
    }

    // Insert if no errors
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO feedback_entries (name, email, message, submitted_at) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$name, $email, $message]);
            $success = true;
            $name = $email = $message = '';
        } catch (PDOException $e) {
            $errors['db'] = "Failed to save feedback. Please try again later.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Feedback Form - Risky Fighter</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" />
    <style>
        *, *::before, *::after {
            box-sizing: border-box;
        }
        body {
            margin: 0; font-family: 'Inter', sans-serif; background-color: #f9fafb; color: #374151;
            display: flex; justify-content: center; align-items: flex-start; min-height: 100vh; padding: 2rem;
        }
        .container {
            background: #ffffff; padding: 2rem 2.5rem; border-radius: 16px; box-shadow: 0 4px 16px rgba(0,0,0,0.1);
            max-width: 480px; width: 100%;
        }
        h1 {
            font-weight: 700; font-size: 2rem; color: #111827; margin-bottom: 1rem; text-align: center;
        }
        form {
            display: flex; flex-direction: column; gap: 1.5rem;
        }
        label {
            font-weight: 600; margin-bottom: 0.25rem; display: block; color: #374151;
        }
        input[type=text],
        input[type=email],
        textarea {
            width: 100%; padding: 0.75rem 1rem; border: 1.5px solid #d1d5db; border-radius: 12px;
            font-size: 1rem; font-family: inherit; color: #111827; transition: border-color 0.3s ease;
            resize: vertical;
        }
        input[type=text]:focus,
        input[type=email]:focus,
        textarea:focus {
            border-color: #4f46e5; outline: none; box-shadow: 0 0 0 3px rgba(99,102,241,0.3);
        }
        textarea {
            min-height: 120px;
        }
        .error-message {
            color: #dc2626; font-size: 0.875rem; margin-top: 0.25rem;
        }
        .success-message {
            background-color: #d1fae5; border: 1px solid #10b981; color: #065f46; padding: 1rem 1.25rem;
            border-radius: 12px; font-weight: 600; font-size: 1rem; margin-bottom: 1.5rem; text-align: center;
        }
        button.submit-button {
            background-color: #4f46e5; color: white; font-weight: 600; padding: 0.85rem 0; border: none;
            border-radius: 12px; cursor: pointer; font-size: 1.125rem; display:flex; align-items:center; justify-content:center; gap:8px;
            transition: background-color 0.3s ease;
            user-select: none;
            min-height: 48px;
        }
        button.submit-button:hover,
        button.submit-button:focus-visible {
            background-color: #4338ca; outline-offset: 3px;
        }
        .material-icons {
            font-size: 20px; vertical-align: middle;
        }
    </style>
</head>
<body>
    <main class="container" role="main" aria-label="Feedback Form">
        <h1>Feedback Form</h1>

        <?php if ($success): ?>
            <div class="success-message" role="alert" tabindex="0">
                Thank you! Your feedback has been submitted successfully.
            </div>
        <?php endif; ?>

        <?php if (isset($errors['db'])): ?>
            <div class="error-message" role="alert" tabindex="0">
                <?= htmlspecialchars($errors['db']) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?= htmlspecialchars($_SERVER['PHP_SELF']) ?>" novalidate>
            <div>
                <label for="name">Full Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    value="<?= htmlspecialchars($name) ?>"
                    aria-describedby="name-error"
                    required maxlength="100"
                    placeholder="Your full name"
                    autocomplete="name"
                />
                <?php if (isset($errors['name'])): ?>
                    <div class="error-message" id="name-error"><?= htmlspecialchars($errors['name']) ?></div>
                <?php endif; ?>
            </div>

            <div>
                <label for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="<?= htmlspecialchars($email) ?>"
                    aria-describedby="email-error"
                    required maxlength="100"
                    placeholder="you@example.com"
                    autocomplete="email"
                />
                <?php if (isset($errors['email'])): ?>
                    <div class="error-message" id="email-error"><?= htmlspecialchars($errors['email']) ?></div>
                <?php endif; ?>
            </div>

            <div>
                <label for="message">Your Feedback</label>
                <textarea
                    id="message"
                    name="message"
                    aria-describedby="message-error"
                    required maxlength="1000"
                    placeholder="Write your feedback here..."
                ><?= htmlspecialchars($message) ?></textarea>
                <?php if (isset($errors['message'])): ?>
                    <div class="error-message" id="message-error"><?= htmlspecialchars($errors['message']) ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="submit-button" aria-label="Submit Feedback">
                <span class="material-icons" aria-hidden="true">send</span>
                Send Feedback
            </button>
        </form>
    </main>
</body>
</html>

