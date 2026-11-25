<?php
// Controller: Waste Report (Front Office form + Back Office management)
class WasteReportController
{
    private function csrf(): string
    {
        return $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
    }

    private function checkCsrf(): void
    {
        if (!hash_equals($this->csrf(), $_POST['csrf'] ?? '')) { http_response_code(400); exit('Invalid form token.'); }
    }

    // ---- Front Office ----
    public function index(array $values = [], array $errors = []): void
    {
        view('front/waste_report/report', [
            'title'  => 'Report Waste',
            'values' => $values + ['cat' => '', 'loc' => '', 'desc' => '', 'pref' => WasteReport::PREFERENCES[0]],
            'errors' => $errors,
            'mine'   => WasteReport::mine(),
            'csrf'   => $this->csrf(),
            'flash'  => flash(),
        ]);
    }

    public function store(): void
    {
        
        $values = [];
        foreach (['cat', 'loc', 'desc', 'pref'] as $k) $values[$k] = trim((string)($_POST[$k] ?? ''));
        $errors = WasteReport::validate($values);
        if ($errors) { http_response_code(422); $this->index($values, $errors); return; }
        $r = WasteReport::create($values);
        flash("Report {$r['id']} submitted. Thank you!");
        redirect('report');                       // Post/Redirect/Get
    }

    // ---- Back Office ----
    public function adminList(): void
    {
        $q = trim((string)($_GET['q'] ?? ''));
        $status = in_array($_GET['status'] ?? '', WasteReport::STATUSES, true) ? $_GET['status'] : 'All';
        view('back/waste_report/list', [
            'title' => 'Waste Reports', 'q' => $q, 'status' => $status,
            'list' => WasteReport::search($q, $status), 'flash' => flash(),
        ]);
    }

    public function adminShow(): void
    {
        $report = WasteReport::find((string)($_GET['id'] ?? ''));
        if (!$report) { http_response_code(404); exit('Report not found.'); }
        view('back/waste_report/detail', ['title' => 'Report ' . $report['id'], 'report' => $report, 'csrf' => $this->csrf(), 'flash' => flash()]);
    }

    public function adminStatus(): void
    {
        
        $id = (string)($_POST['id'] ?? '');
        $ok = WasteReport::setStatus($id, (string)($_POST['status'] ?? ''));
        flash($ok ? "$id updated." : 'Could not update this report.');
        redirect('admin.report', ['id' => $id]);
    }
        public function apiReports(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        if (!empty($_GET['id'])) { echo json_encode(WasteReport::find((string)$_GET['id'])); return; }
        echo json_encode(!empty($_GET['mine']) ? WasteReport::mine() : WasteReport::all());
    }
}
