<?php
require_once '../vendor/autoload.php'; // Adjust path to TCPDF

// Initializing PDF document
use TCPDF;

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Setting document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('MSSB Driving Institute');
$pdf->SetTitle('Analytics Report');
$pdf->SetSubject('Dashboard Analytics');
$pdf->SetKeywords('analytics, report, students, instructors, payments, progress');

// Setting margins
$pdf->SetMargins(15, 15, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);

// Setting auto page breaks
$pdf->SetAutoPageBreak(TRUE, 15);

// Setting font
$pdf->SetFont('helvetica', '', 12);

// Adding a page
$pdf->AddPage();

// Processing POST data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid input']);
    exit;
}

$totalStudents = $input['totalStudents'] ?? 0;
$totalInstructors = $input['totalInstructors'] ?? 0;
$totalPayments = $input['totalPayments'] ?? '0.00';
$avgProgress = $input['avgProgress'] ?? 0;
$progressDistribution = $input['progressDistribution'] ?? [0, 0, 0, 0, 0];
$paymentStatusDistribution = $input['paymentStatusDistribution'] ?? ['Pending' => 0, 'Completed' => 0, 'Failed' => 0];
$registrationLabels = $input['registrationLabels'] ?? [];
$registrationCounts = $input['registrationCounts'] ?? [];
$progressImage = $input['progressImage'] ?? '';
$paymentImage = $input['paymentImage'] ?? '';
$registrationImage = $input['registrationImage'] ?? '';

// Writing report title
$pdf->SetFont('helvetica', 'B', 16);
$pdf->Cell(0, 10, 'Analytics Report', 0, 1, 'C');
$pdf->SetFont('helvetica', '', 12);
$pdf->Ln(5);

// Adding summary section
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Summary', 0, 1);
$pdf->SetFont('helvetica', '', 12);
$pdf->Cell(0, 8, "Total Students: $totalStudents", 0, 1);
$pdf->Cell(0, 8, "Total Instructors: $totalInstructors", 0, 1);
$pdf->Cell(0, 8, "Total Payments: $$totalPayments", 0, 1);
$pdf->Cell(0, 8, "Average Progress: $avgProgress%", 0, 1);
$pdf->Ln(5);

// Adding progress distribution
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Student Progress Distribution', 0, 1);
if (array_sum($progressDistribution) == 0) {
    $pdf->SetFont('helvetica', 'I', 12);
    $pdf->Cell(0, 8, 'No progress data available', 0, 1);
} else {
    $pdf->SetFont('helvetica', '', 12);
    $labels = ['0-20%', '21-40%', '41-60%', '61-80%', '81-100%'];
    foreach ($labels as $i => $label) {
        $pdf->Cell(0, 8, "$label: {$progressDistribution[$i]} students", 0, 1);
    }
    if ($progressImage) {
        $pdf->Image('@' . base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $progressImage)), '', '', 100, 0, 'PNG');
    }
}
$pdf->Ln(5);

// Adding payment status distribution
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Payment Status Distribution', 0, 1);
if (array_sum($paymentStatusDistribution) == 0) {
    $pdf->SetFont('helvetica', 'I', 12);
    $pdf->Cell(0, 8, 'No payment data available', 0, 1);
} else {
    $pdf->SetFont('helvetica', '', 12);
    foreach (['Pending', 'Completed', 'Failed'] as $status) {
        $count = $paymentStatusDistribution[$status] ?? 0;
        $pdf->Cell(0, 8, "$status: $count", 0, 1);
    }
    if ($paymentImage) {
        $pdf->Image('@' . base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $paymentImage)), '', '', 100, 0, 'PNG');
    }
}
$pdf->Ln(5);

// Adding registration data
$pdf->SetFont('helvetica', 'B', 14);
$pdf->Cell(0, 10, 'Student Registrations Over Time', 0, 1);
if (empty($registrationLabels)) {
    $pdf->SetFont('helvetica', 'I', 12);
    $pdf->Cell(0, 8, 'No registration data available', 0, 1);
} else {
    $pdf->SetFont('helvetica', '', 12);
    foreach ($registrationLabels as $i => $date) {
        $count = $registrationCounts[$i] ?? 0;
        $pdf->Cell(0, 8, "$date: $count registrations", 0, 1);
    }
    if ($registrationImage) {
        $pdf->Image('@' . base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $registrationImage)), '', '', 100, 0, 'PNG');
    }
}

// Outputting PDF
$pdf->Output('Analytics_Report_' . date('Y-m-d') . '.pdf', 'D');
?>