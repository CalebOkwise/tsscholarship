<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/functions.php';
require_once dirname(__DIR__) . '/config/mail.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect_to('../index.html#apply');
}

start_secure_session();

if (clean_string($_POST['website'] ?? '') !== '') {
    mark_submission_time();
    redirect_to('../thank-you.html?success=1#apply');
}

try {
    $pdo = db();
    $ip = client_ip();

    if (is_rate_limited($pdo, $ip)) {
        redirect_to('../index.html?error=rate_limited#apply');
    }

    $firstName = clean_string($_POST['firstName'] ?? '', 120);
    $lastName = clean_string($_POST['lastName'] ?? '', 120);
    $email = filter_var(trim((string) ($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
    $phone = clean_string($_POST['phone'] ?? '', 60);
    $whatsapp = clean_string($_POST['whatsapp'] ?? '', 60);
    $gender = clean_string($_POST['gender'] ?? '', 40);
    $ageRange = clean_string($_POST['age'] ?? '', 40);
    $state = clean_string($_POST['state'] ?? '', 120);
    $qualification = clean_string($_POST['qualification'] ?? '', 180);
    $occupation = clean_string($_POST['occupation'] ?? '', 180);
    $employment = clean_string($_POST['employment'] ?? '', 80);
    $course = clean_string($_POST['course'] ?? '', 120);
    $interest = clean_text($_POST['interest'] ?? '', 2500);
    $goals = clean_text($_POST['goals'] ?? '', 2500);
    $experience = clean_string($_POST['experience'] ?? '', 20);
    $heardFrom = clean_string($_POST['source'] ?? '', 120);
    $consent = isset($_POST['consent']);

    $allowedCourses = ['Cybersecurity', 'Web Development', 'Digital Marketing', 'DevOps', 'Data Analytics'];
    $allowedExperience = ['Yes', 'No'];

    if ($firstName === '' || $lastName === '' || !$email || $phone === '' || $course === '' || !$consent) {
        redirect_to('../index.html?error=validation#apply');
    }

    if (!in_array($course, $allowedCourses, true) || !in_array($experience, $allowedExperience, true)) {
        redirect_to('../index.html?error=validation#apply');
    }

    $fullName = trim($firstName . ' ' . $lastName);
    $source = build_source([
        'source' => $heardFrom,
        'landing_page' => $_POST['landing_page'] ?? 'teamsource-scholarship',
        'utm_source' => $_POST['utm_source'] ?? '',
        'utm_medium' => $_POST['utm_medium'] ?? '',
        'utm_campaign' => $_POST['utm_campaign'] ?? '',
        'utm_content' => $_POST['utm_content'] ?? '',
        'utm_term' => $_POST['utm_term'] ?? '',
    ]);

    $message = "Why interested:\n{$interest}\n\nCareer goals:\n{$goals}\n\nHeard from:\n{$heardFrom}";

    $stmt = $pdo->prepare(
        'INSERT INTO leads (
            full_name, first_name, last_name, email, phone, whatsapp, gender, age_range,
            state_of_residence, highest_qualification, current_occupation, employment_status,
            preferred_course, prior_tech_experience, career_goals, message, source, ip_address, user_agent, status
        ) VALUES (
            :full_name, :first_name, :last_name, :email, :phone, :whatsapp, :gender, :age_range,
            :state_of_residence, :highest_qualification, :current_occupation, :employment_status,
            :preferred_course, :prior_tech_experience, :career_goals, :message, :source, :ip_address, :user_agent, :status
        )'
    );

    $lead = [
        'full_name' => $fullName,
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => (string) $email,
        'phone' => $phone,
        'whatsapp' => $whatsapp,
        'gender' => $gender,
        'age_range' => $ageRange,
        'state_of_residence' => $state,
        'highest_qualification' => $qualification,
        'current_occupation' => $occupation,
        'employment_status' => $employment,
        'preferred_course' => $course,
        'prior_tech_experience' => $experience,
        'career_goals' => $goals,
        'message' => $message,
        'source' => $source,
        'ip_address' => $ip,
        'user_agent' => user_agent(),
        'status' => 'new',
    ];

    $params = $lead;
    unset($params['created_at']);
    $stmt->execute($params);
    mark_submission_time();

    send_lead_notification($lead);

    redirect_to('../thank-you.html?success=1#apply');
} catch (Throwable $throwable) {
    error_log('Lead submission failed: ' . $throwable->getMessage());
    redirect_to('../index.html?error=server#apply');
}


