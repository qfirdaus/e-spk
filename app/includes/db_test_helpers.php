<?php
// includes/db_test_helpers.php
// Helper functions untuk database testing page

declare(strict_types=1);

require_once __DIR__ . '/../classes/SystemConfigConstants.php';

/**
 * HTML escape helper
 */
if (!function_exists('db_test_h')) {
    function db_test_h($v): string {
        return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
    }
}

/**
 * User-friendly error translation
 */
if (!function_exists('db_test_translate_error')) {
    function db_test_translate_error(string $error): string {
        $errorLower = strtolower($error);
        
        if (str_contains($errorLower, 'could not connect') || str_contains($errorLower, 'no route to host')) {
            return __('ujian_db_error_cannot_connect') ?: 'Tidak dapat menyambung ke server database. Sila semak host dan port.';
        }
        if (str_contains($errorLower, 'login failed') || str_contains($errorLower, 'access denied') || str_contains($errorLower, 'authentication')) {
            return __('ujian_db_error_access_denied') ?: 'Akses ditolak. Sila semak kredensial (username/katalaluan).';
        }
        if (str_contains($errorLower, 'unknown database') || (str_contains($errorLower, 'database') && str_contains($errorLower, 'not found'))) {
            return __('ujian_db_error_database_not_found') ?: 'Database tidak dijumpai. Sila semak nama database.';
        }
        if (str_contains($errorLower, 'connection timed out') || str_contains($errorLower, 'timeout')) {
            return __('ujian_db_error_timeout') ?: 'Sambungan tamat masa. Server mungkin tidak responsif atau firewall menyekat.';
        }
        if (str_contains($errorLower, 'dsn') && str_contains($errorLower, 'not found')) {
            return __('ujian_db_error_dsn_not_found') ?: 'DSN tidak dijumpai. Sila pasang driver ODBC atau semak konfigurasi DSN.';
        }
        if (str_contains($errorLower, 'driver not found') || str_contains($errorLower, 'could not find driver')) {
            return __('ujian_db_error_driver_not_found') ?: 'Driver tidak dijumpai. Sila pasang driver yang diperlukan (ODBC atau DBLIB).';
        }
        if (str_contains($errorLower, 'connection refused')) {
            return __('ujian_db_error_connection_refused') ?: 'Sambungan ditolak. Server mungkin tidak berjalan atau port tidak betul.';
        }
        
        // Generic fallback
        return __('ujian_db_error_generic') ?: 'Sambungan gagal. Sila hubungi pentadbir sistem untuk bantuan.';
    }
}

/**
 * Render success connection cards
 * 
 * Displays successful database connections dalam card format dengan:
 * - Connection name, platform, driver, server info
 * - Response time dengan performance indicators
 * - Individual test button (optional)
 * - View details button (optional)
 * 
 * @param array $list Array of successful connection results
 * @param bool $showTestButton Show individual test button (default: true)
 * @return void
 */
if (!function_exists('db_test_render_ok_cards')) {
    function db_test_render_ok_cards(array $list, bool $showTestButton = true): void {
        foreach ($list as $item): ?>
            <div class="col-md-4">
                <div class="card card-ujian shadow-sm border-success">
                    <div class="card-body d-flex align-items-center">
                        <div class="text-success ujian-icon"><i class="ri-database-line"></i></div>
                        <div class="flex-grow-1">
                            <div class="ujian-header d-flex justify-content-between align-items-center">
                                <span><?= db_test_h($item['name'] ?? '') ?></span>
                                <?php if ($showTestButton): ?>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary btn-test-single ms-2" 
                                            data-connection="<?= db_test_h($item['name'] ?? '') ?>"
                                            title="<?= __('ujian_db_test_single_tooltip') ?: 'Uji sambungan ini' ?>"
                                            aria-label="<?= __('ujian_db_test_single') ?: 'Uji' ?>">
                                        <i class="ri-refresh-line" aria-hidden="true"></i>
                                    </button>
                                <?php endif; ?>
                            </div>
                            <div class="ujian-sub mb-1">
                                <?= db_test_h($item['platform'] ?? '') ?>
                                <?php if (!empty($item['driver'])): ?> | <?= db_test_h($item['driver']) ?><?php endif; ?>
                                <?php if (!empty($item['server'])): ?> | <?= db_test_h($item['server']) ?><?php endif; ?>
                                <?php if (!empty($item['server_time'])): ?> | <?= db_test_h($item['server_time']) ?><?php endif; ?>
                                <?php if (!empty($item['response_time_ms'])): ?>
                                    <br><small class="text-muted">
                                        <i class="ri-time-line me-1"></i><?= __('ujian_db_response') ?: 'Response' ?>: <strong><?= db_test_h($item['response_time_ms']) ?>ms</strong>
                                        <?php 
                                            $responseTime = (float)($item['response_time_ms'] ?? 0);
                                            $thresholdSlow = SystemConfigConstants::DB_TEST_RESPONSE_TIME_SLOW;
                                            $thresholdFast = SystemConfigConstants::DB_TEST_RESPONSE_TIME_FAST;
                                            if ($responseTime > $thresholdSlow): 
                                        ?>
                                            <span class="badge bg-warning-subtle text-warning ms-1"><?= __('ujian_db_perf_slow') ?: 'Slow' ?></span>
                                        <?php elseif ($responseTime > $thresholdFast): ?>
                                            <span class="badge bg-info-subtle text-info ms-1"><?= __('ujian_db_perf_moderate') ?: 'Moderate' ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-success-subtle text-success ms-1"><?= __('ujian_db_perf_fast') ?: 'Fast' ?></span>
                                        <?php endif; ?>
                                    </small>
                                <?php endif; ?>
                            </div>
                            <div class="text-success small fst-italic">
                                <?= __('nota_db_berjaya') ?: 'Sambungan berjaya.' ?>
                            </div>
                            <?php if ($showTestButton): ?>
                                <button type="button" 
                                        class="btn btn-sm btn-link text-decoration-none p-0 mt-1 btn-view-details" 
                                        data-connection="<?= db_test_h($item['name'] ?? '') ?>"
                                        data-result='<?= json_encode($item, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_APOS) ?>'
                                        title="<?= __('ujian_db_view_details_tooltip') ?: 'Lihat butiran sambungan' ?>"
                                        aria-label="<?= __('ujian_db_view_details') ?: 'Butiran' ?>">
                                    <small><i class="ri-information-line me-1" aria-hidden="true"></i><?= __('ujian_db_view_details') ?: 'Butiran' ?></small>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach;
    }
}

/**
 * Render failed connection cards
 * 
 * Displays failed database connections dalam card format dengan:
 * - Connection name, platform info
 * - Translated error message
 * - Response time (jika available)
 * - Troubleshooting hints
 * - Individual test button (optional)
 * - View details button (optional)
 * 
 * @param array $list Array of failed connection results
 * @param bool $showTestButton Show individual test button (default: true)
 * @return void
 */
if (!function_exists('db_test_render_ng_cards')) {
    function db_test_render_ng_cards(array $list, bool $showTestButton = true): void {
        foreach ($list as $item): 
            $responseTime = (float)($item['response_time_ms'] ?? 0);
            $thresholdSlow = SystemConfigConstants::DB_TEST_RESPONSE_TIME_SLOW;
            $thresholdFast = SystemConfigConstants::DB_TEST_RESPONSE_TIME_FAST;
            $perfClass = $responseTime > $thresholdSlow ? 'warning' : ($responseTime > $thresholdFast ? 'info' : 'success');
            $perfLabel = $responseTime > $thresholdSlow ? __('ujian_db_perf_slow') : ($responseTime > $thresholdFast ? __('ujian_db_perf_moderate') : __('ujian_db_perf_fast'));
        ?>
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100" style="border-left: 4px solid #dc3545 !important; transition: all 0.3s ease;">
                    <div class="card-body p-4">
                        <!-- Header dengan icon dan action button -->
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div class="d-flex align-items-center">
                                <div class="bg-danger bg-opacity-10 rounded-circle p-3 me-3" style="width: 56px; height: 56px; display: flex; align-items: center; justify-content: center;">
                                    <i class="ri-database-2-line text-danger" style="font-size: 24px;"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 fw-bold text-dark"><?= db_test_h($item['name'] ?? '') ?></h6>
                                    <small class="text-muted"><?= db_test_h($item['platform'] ?? '-') ?></small>
                                </div>
                            </div>
                            <?php if ($showTestButton): ?>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary btn-test-single rounded-circle" 
                                        style="width: 36px; height: 36px; padding: 0;"
                                        data-connection="<?= db_test_h($item['name'] ?? '') ?>"
                                        title="<?= __('ujian_db_test_single_tooltip') ?: 'Uji sambungan ini' ?>"
                                        aria-label="<?= __('ujian_db_test_single') ?: 'Uji' ?>">
                                    <i class="ri-refresh-line" aria-hidden="true"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Connection Details -->
                        <div class="mb-3">
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <?php if (!empty($item['driver'])): ?>
                                    <span class="badge bg-light text-dark border">
                                        <i class="ri-code-line me-1"></i><?= db_test_h($item['driver']) ?>
                                    </span>
                                <?php endif; ?>
                                <?php if (!empty($item['kind'])): ?>
                                    <span class="badge bg-light text-dark border">
                                        <i class="ri-stack-line me-1"></i><?= db_test_h($item['kind']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if (!empty($item['server'])): ?>
                                <div class="mb-2">
                                    <small class="text-muted d-block mb-1">
                                        <i class="ri-server-line me-1"></i><?= __('ujian_db_modal_server') ?: 'Server' ?>
                                    </small>
                                    <code class="text-dark"><?= db_test_h($item['server']) ?></code>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Response Time dengan Performance Indicator -->
                        <?php if (!empty($item['response_time_ms'])): ?>
                            <div class="d-flex justify-content-between align-items-center p-3 rounded mb-3" style="background: #f8f9fa;">
                                <div>
                                    <small class="text-muted d-block mb-1"><?= __('ujian_db_response') ?: 'Response Time' ?></small>
                                    <h5 class="mb-0 fw-bold text-dark"><?= db_test_h($item['response_time_ms']) ?>ms</h5>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-<?= $perfClass ?>-subtle text-<?= $perfClass ?> px-3 py-2">
                                        <i class="ri-speed-line me-1"></i><?= $perfLabel ?>
                                    </span>
                                    <?php if (!empty($item['timeout'])): ?>
                                        <span class="badge bg-danger-subtle text-danger px-3 py-2 ms-1">
                                            <i class="ri-time-outline me-1"></i><?= __('ujian_db_timeout') ?: 'Timeout' ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Error Message (compact) -->
                        <div class="mb-3">
                            <small class="text-muted d-block mb-1">
                                <i class="ri-alert-line me-1"></i><?= __('ujian_db_modal_error') ?: 'Ralat' ?>
                            </small>
                            <p class="mb-0 small text-danger"><?= nl2br(db_test_h(db_test_translate_error($item['error'] ?? ''))) ?></p>
                        </div>
                        
                        <!-- Status Badge -->
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-danger-subtle text-danger px-3 py-2">
                                <i class="ri-close-circle-line me-1"></i><?= __('nota_db_gagal') ?: 'Sambungan gagal' ?>
                            </span>
                            <?php if ($showTestButton): ?>
                                <button type="button" 
                                        class="btn btn-sm btn-link text-primary text-decoration-none p-0 btn-view-details" 
                                        data-connection="<?= db_test_h($item['name'] ?? '') ?>"
                                        data-result='<?= json_encode($item, JSON_UNESCAPED_UNICODE | JSON_HEX_QUOT | JSON_HEX_APOS) ?>'
                                        title="<?= __('ujian_db_view_details_tooltip') ?: 'Lihat butiran sambungan' ?>"
                                        aria-label="<?= __('ujian_db_view_details') ?: 'Butiran' ?>">
                                    <i class="ri-information-line me-1" aria-hidden="true"></i><?= __('ujian_db_view_details') ?: 'Butiran' ?>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach;
    }
}

