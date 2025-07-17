<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userMessage = trim($_POST['message']);

    // Database connection
    require '../db/db_connect.php';

    if (!isset($conn) || !$conn) {
        echo "⚠️ Database Connection Error: Connection not established.";
        exit;
    }

    // Function to check if the query is database-related
    function isDatabaseQuery($message) {
        $keywords = [
            'my score', 'my progress', 'my payment', 'my status', 'exam score', 'course progress', 'payment status',
            'how many student', 'how many registered', 'how many paid', 'how much paid', 'total payment', 'students registered', 'my name',
            'how many instructors', 'how many students pass', 'passing rate', 'requirements', 'what is tdc', 'how many hours to complete tdc'
        ];
        foreach ($keywords as $keyword) {
            if (stripos($message, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }

    // Function to check if the query is user-specific
    function isUserSpecificQuery($message) {
        $keywords = ['my score', 'my progress', 'my payment', 'my status', 'exam score', 'course progress', 'payment status', 'name'];
        foreach ($keywords as $keyword) {
            if (stripos($message, $keyword) !== false) {
                return true;
            }
        }
        return false;
    }

    // Handle database-related queries
    if (isDatabaseQuery($userMessage)) {
        // Check if user is logged in for user-specific queries
        if (isUserSpecificQuery($userMessage) && !isset($_SESSION['user_id'])) {
            echo "⚠️ Please log in to access your personal data.";
            exit;
        }

        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

        // Check exam scores
        if (stripos($userMessage, 'my score') !== false || stripos($userMessage, 'exam score') !== false) {
            $stmt = $conn->prepare("SELECT type, score FROM exam_scores WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $scores = $result->fetch_all(MYSQLI_ASSOC);

            if ($scores) {
                $response = "Your exam scores:\n";
                foreach ($scores as $score) {
                    $response .= "- {$score['type']}: {$score['score']}\n";
                }
                echo $response;
                $stmt->close();
                exit;
            } else {
                echo "No exam scores found for your account.";
                $stmt->close();
                exit;
            }
        }

        // Check course progress
        if (stripos($userMessage, 'my progress') !== false || stripos($userMessage, 'course progress') !== false) {
            $stmt = $conn->prepare("SELECT lesson_id, course_name, completion_percentage, test_1_score, test_2_score, final_test_score FROM progress WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $progress = $result->fetch_all(MYSQLI_ASSOC);

            if ($progress) {
                $response = "Your course progress:\n";
                foreach ($progress as $entry) {
                    $courseName = $entry['course_name'] ?? 'Lesson ' . $entry['lesson_id'];
                    $response .= "- $courseName: {$entry['completion_percentage']}% completed\n";
                    if ($entry['test_1_score']) $response .= "  Test 1: {$entry['test_1_score']}\n";
                    if ($entry['test_2_score']) $response .= "  Test 2: {$entry['test_2_score']}\n";
                    if ($entry['final_test_score']) $response .= "  Final Test: {$entry['final_test_score']}\n";
                }
                echo $response;
                $stmt->close();
                exit;
            } else {
                echo "No progress data found for your account.";
                $stmt->close();
                exit;
            }
        }

        // Check payment status
        if (stripos($userMessage, 'my payment') !== false || stripos($userMessage, 'payment status') !== false) {
            $stmt = $conn->prepare("SELECT amount, payment_date, status, payment_method FROM payments WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $payments = $result->fetch_all(MYSQLI_ASSOC);

            if ($payments) {
                $response = "Your payment history:\n";
                foreach ($payments as $payment) {
                    $response .= "- Amount: {$payment['amount']}, Date: {$payment['payment_date']}, Status: {$payment['status']}, Method: {$payment['payment_method']}\n";
                }
                echo $response;
                $stmt->close();
                exit;
            } else {
                echo "No payment records found for your account.";
                $stmt->close();
                exit;
            }
        }

        // Check user status
        if (stripos($userMessage, 'my status') !== false) {
            $stmt = $conn->prepare("SELECT status, test_1_score, test_2_score, final_test_score FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user) {
                $response = "Your account status: " . ($user['status'] ?? 'Not set') . "\n";
                if ($user['test_1_score']) $response .= "Test 1 Score: {$user['test_1_score']}\n";
                if ($user['test_2_score']) $response .= "Test 2 Score: {$user['test_2_score']}\n";
                if ($user['final_test_score']) $response .= "Final Test Score: {$user['final_test_score']}\n";
                echo $response;
                $stmt->close();
                exit;
            } else {
                echo "No user data found for your account.";
                $stmt->close();
                exit;
            }
        }

        // How many students are registered
        if (stripos($userMessage, 'how many registered') !== false || stripos($userMessage, 'students registered') !== false) {
            $stmt = $conn->prepare("SELECT COUNT(*) as student_count FROM users WHERE role = 'student'");
            $stmt->execute();
            $result = $stmt->get_result();
            $result = $result->fetch_assoc();
            echo "Total registered students: " . $result['student_count'];
            $stmt->close();
            exit;
        }

        // How many instructors are registered
        if (stripos($userMessage, 'how many instructors') !== false) {
            $stmt = $conn->prepare("SELECT COUNT(*) as instructor_count FROM users WHERE role = 'instructor'");
            $stmt->execute();
            $result = $stmt->get_result();
            $result = $result->fetch_assoc();
            echo "Total registered instructors: " . $result['instructor_count'];
            $stmt->close();
            exit;
        }

        // How many students have passed (80% or higher)
        if (stripos($userMessage, 'how many students pass') !== false) {
            $passingScore = 80;
            $stmt = $conn->prepare("SELECT COUNT(*) as passed_count FROM users WHERE role = 'student' AND final_test_score >= ?");
            $stmt->bind_param("i", $passingScore);
            $stmt->execute();
            $result = $stmt->get_result();
            $result = $result->fetch_assoc();
            echo "Total students who have passed with 80% or higher: " . $result['passed_count'];
            $stmt->close();
            exit;
        }

        // Passing rate for TDC (fixed at 80%)
        if (stripos($userMessage, 'passing rate') !== false) {
            echo "The passing rate for the Theoretical Driving Course is 80%.";
            exit;
        }

        // Requirements for TDC
        if (stripos($userMessage, 'requirements') !== false) {
            echo "Requirements for the Theoretical Driving Course include: an original PSA birth certificate or PSA marriage certificate, one valid government-issued ID, a vaccination card, and a black or blue-inked pen.";
            exit;
        }

        // General TDC information
        if (stripos($userMessage, 'what is tdc') !== false) {
            echo "The Theoretical Driving Course (TDC) is a 15-hour mandatory program by the Land Transportation Office (LTO) in the Philippines, covering driving basics and road safety. It’s required to obtain a Student-Driver’s Permit.";
            exit;
        }

        // How many hours to complete TDC
        if (stripos($userMessage, 'how many hours to complete tdc') !== false) {
            echo "The Theoretical Driving Course (TDC) takes 15 hours to complete.";
            exit;
        }

        // How many students have paid
        if (stripos($userMessage, 'how many paid') !== false) {
            $stmt = $conn->prepare("SELECT COUNT(DISTINCT user_id) as paid_count FROM payments WHERE status = 'completed'");
            $stmt->execute();
            $result = $stmt->get_result();
            $result = $result->fetch_assoc();
            echo "Total students who have paid: " . $result['paid_count'];
            $stmt->close();
            exit;
        }

        // How much was paid this month
        if (stripos($userMessage, 'how much paid') !== false || stripos($userMessage, 'total payment') !== false) {
            $currentMonth = date('Y-m');
            $stmt = $conn->prepare("SELECT SUM(amount) as total_paid FROM payments WHERE status = 'completed' AND DATE_FORMAT(payment_date, '%Y-%m') = ?");
            $stmt->bind_param("s", $currentMonth);
            $stmt->execute();
            $result = $stmt->get_result();
            $result = $result->fetch_assoc();
            $totalPaid = $result['total_paid'] ?? 0;
            echo "Total payments for this month: $" . number_format($totalPaid, 2);
            $stmt->close();
            exit;
        }
    }

    // Fallback to Gemini API for driving-related queries
    $apiKey = "AIzaSyDxMNQoc8_2bWRXvs9dI8SDSrnbAVqEzjY"; // Replace with your API key
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=$apiKey";

    $postData = [
        "contents" => [
            [
                "role" => "user",
                "parts" => [
                    ["text" => "You are an expert in driving topics like car maintenance, traffic rules, safe driving, and vehicles. Only answer if it's about driving. If it's unrelated, politely decline."],
                    ["text" => $userMessage]
                ]
            ]
        ]
    ];

    $payload = json_encode($postData);

    // cURL request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo "⚠️ Request Error: " . curl_error($ch);
        curl_close($ch);
        exit;
    }

    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
        echo html_entity_decode($result['candidates'][0]['content']['parts'][0]['text']);
    } else {
        if (isset($result['error']['message'])) {
            echo "⚠️ API Error: " . html_entity_decode($result['error']['message']);
        } else {
            echo "🚧 Sorry, I couldn't get a proper answer. Try again later!";
        }
    }
}

$conn->close();
?>