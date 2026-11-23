<?php

namespace App\Controllers;

use App\Core\Request;
use App\Services\EmailService;
use Exception;
use InvalidArgumentException;

class EmailController extends BaseController
{
    private EmailService $emailService;

    public function __construct()
    {
        $this->emailService = new EmailService();
    }

    // -------------------------------------------------------------------------
    // SETTINGS (CREDENTIALS)
    // -------------------------------------------------------------------------

    public function getSettings(Request $request): void
    {
        try {
            $settings = $this->emailService->getSettings();
            $this->ok($settings, 'Email settings retrieved');
        } catch (Exception $e) {
            $this->serverError($e->getMessage());
        }
    }

    public function createSetting(Request $request): void
    {
        $actor = $request->getUser() ?? [];
        try {
            $res = $this->emailService->saveSetting($request->input(), $actor);
            $this->created($res, 'Email credentials saved successfully');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (Exception $e) {
            $this->serverError('Failed to save email settings', $e->getMessage());
        }
    }

    public function updateSetting(Request $request): void
    {
        $id = (int)$request->param('id');
        $actor = $request->getUser() ?? [];
        try {
            $res = $this->emailService->updateSetting($id, $request->input(), $actor);
            $this->ok($res, 'Email settings updated');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (Exception $e) {
            $this->serverError('Failed to update email settings', $e->getMessage());
        }
    }

    public function deleteSetting(Request $request): void
    {
        $id = (int)$request->param('id');
        $actor = $request->getUser() ?? [];
        try {
            $this->emailService->deleteSetting($id, $actor);
            $this->ok(null, 'Email setting deleted');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (Exception $e) {
            $this->serverError('Failed to delete email setting', $e->getMessage());
        }
    }

    public function testConnection(Request $request): void
    {
        try {
            $result = $this->emailService->testConnection($request->input());
            if ($result['success']) {
                $this->ok($result, $result['message']);
            } else {
                $this->unprocessable($result['message'], $result['diagnostics'] ?? null);
            }
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (Exception $e) {
            $this->serverError('Test dispatch failed', $e->getMessage());
        }
    }

    // -------------------------------------------------------------------------
    // TEMPLATES
    // -------------------------------------------------------------------------

    public function getTemplates(Request $request): void
    {
        try {
            $filters = $request->getQueryParams();
            $templates = $this->emailService->getTemplates($filters);
            $this->ok($templates, 'Email templates retrieved');
        } catch (Exception $e) {
            $this->serverError($e->getMessage());
        }
    }

    public function showTemplate(Request $request): void
    {
        $id = (int)$request->param('id');
        try {
            $template = $this->emailService->getTemplateById($id);
            if (!$template) {
                $this->notFound("Template with ID {$id} not found");
                return;
            }
            $this->ok($template, 'Template retrieved');
        } catch (Exception $e) {
            $this->serverError($e->getMessage());
        }
    }

    public function createTemplate(Request $request): void
    {
        $actor = $request->getUser() ?? [];
        try {
            $res = $this->emailService->createTemplate($request->input(), $actor);
            $this->created($res, 'Email template created');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (Exception $e) {
            $this->serverError('Failed to create template', $e->getMessage());
        }
    }

    public function updateTemplate(Request $request): void
    {
        $id = (int)$request->param('id');
        $actor = $request->getUser() ?? [];
        try {
            $res = $this->emailService->updateTemplate($id, $request->input(), $actor);
            $this->ok($res, 'Email template updated');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (Exception $e) {
            $this->serverError('Failed to update template', $e->getMessage());
        }
    }

    public function deleteTemplate(Request $request): void
    {
        $id = (int)$request->param('id');
        $actor = $request->getUser() ?? [];
        try {
            $this->emailService->deleteTemplate($id, $actor);
            $this->ok(null, 'Template deleted');
        } catch (InvalidArgumentException $e) {
            $this->unprocessable($e->getMessage());
        } catch (Exception $e) {
            $this->serverError('Failed to delete template', $e->getMessage());
        }
    }
}
