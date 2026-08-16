<?php

namespace App\Models;

use App\Models\Traits\HasTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Bug report screenshot attachment.
 *
 * Files are stored on the configured public disk; this model only keeps
 * metadata (path, original name, MIME type, size). Tenant isolation is
 * enforced at the record level via HasTenant and at the storage path level
 * by scoping files under bug-reports/{tenant_id}/{bug_report_id}/.
 */
class BugReportAttachment extends Model
{
    use HasFactory;
    use HasTenant;

    protected $table = 'bug_report_attachments';

    protected $fillable = [
        'tenant_id',
        'bug_report_id',
        'file_path',
        'original_name',
        'mime_type',
        'size',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    // =====================================================================
    // Relations
    // =====================================================================

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function bugReport(): BelongsTo
    {
        return $this->belongsTo(BugReport::class);
    }

    // =====================================================================
    // Boot — File cleanup
    // =====================================================================

    protected static function boot(): void
    {
        parent::boot();

        static::deleting(function (self $attachment) {
            if ($attachment->file_path) {
                Storage::disk('public')->delete($attachment->file_path);
            }
        });
    }

    // =====================================================================
    // Attachment creation
    // =====================================================================

    /**
     * Store a screenshot for a bug report.
     *
     * Authorization, tenant scoping, and filename safety are handled here.
     * Identities come from the BugReport model and the supplied User object,
     * never from request input directly.
     *
     * @throws ValidationException
     */
    public static function attachScreenshot(BugReport $bugReport, User $uploader, UploadedFile $file): self
    {
        // Only the original reporter may attach files to their report.
        abort_unless(
            $bugReport->reporter_id === $uploader->id,
            403,
            'Only the reporter may attach screenshots to this report.'
        );

        Validator::make(
            ['file' => $file],
            [
                'file' => [
                    'required',
                    'file',
                    'image',
                    'mimes:jpeg,png,gif,webp',
                    'mimetypes:image/jpeg,image/png,image/gif,image/webp',
                    'max:5120',
                ],
            ]
        )->validate();

        // Tenant-scoped path prevents cross-tenant file leakage even if a URL
        // is guessed; the generated filename prevents path traversal.
        $path = $file->store(
            "bug-reports/{$bugReport->tenant_id}/{$bugReport->id}",
            'public'
        );

        return self::create([
            'tenant_id' => $bugReport->tenant_id,
            'bug_report_id' => $bugReport->id,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
        ]);
    }

    // =====================================================================
    // URL helper
    // =====================================================================

    public function url(): string
    {
        return Storage::disk('public')->url($this->file_path);
    }
}
