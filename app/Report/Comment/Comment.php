<?php

namespace Packages\Abuse\App\Report\Comment;

use App\Admin\Admin;
use App\Database\Models\Model;
use Packages\Abuse\App\Report\Report;
use Illuminate\Database\Eloquent\Relations;

/**
 * Database representation of Abuse Report Comments.
 */
class Comment
extends Model
{
    public static $singular = 'Abuse Report Comment';
    public static $plural = 'Abuse Report Comments';

    protected $table = 'abuse_report_comments';

    protected $fillable = [
        'body',
    ];

    # Attributes
    /**
     * Determine if the comment was posted by an Administrator.
     *
     * @return bool
     */
    public function isByAdmin()
    {
        $authorType = $this->author()->getMorphType();

        return $this->$authorType == Admin::class;
    }

    # Methods

    # Relationships
    /**
     * The Author of the comment.
     *
     * @return Relations\MorphTo
     */
    public function author()
    {
        return $this->morphTo();
    }

    /**
     * The report that the comment was posted on.
     *
     * @return Relations\BelongsTo
     */
    public function report()
    {
        return $this->belongsTo(Report::class, 'abuse_report_id');
    }

    # Scopes
}
