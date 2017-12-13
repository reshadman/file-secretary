<?php

namespace Reshadman\FileSecretary\Application;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Reshadman\FileSecretary\Application\Usecases\DeleteTrackedFile;
use Reshadman\FileSecretary\Infrastructure\FileSecretaryManager;
use Reshadman\FileSecretary\Infrastructure\UrlGenerator;

/**
 * Class EloquentPersistedFile
 * @method hashIs($md5, $sha1)
 * @package Reshadman\FileSecretary\Infrastructure
 */
class EloquentPersistedFile extends Model implements PersistableFile
{
    use PersistableFileTrait;

    protected $guarded = ['id'];

    protected $appends = ['full_url', 'image_templates'];

    protected $hidden = ['generated_templates'];

    /**
     * EloquentPersistedFile constructor.
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        if ($this->table === null) {
            $this->table = config('file_secretary.eloquent.table');
        }

        parent::__construct($attributes);
    }

    protected static function boot()
    {
        parent::boot();
    }

    /**
     * The unique identifier think of it as PK
     *
     * @return string
     */
    public function getFileableIdentifier()
    {
        return $this['id'];
    }

    /**
     * Uuid of the file. Exposed to users, as we should not expose PK's or any type of ids to the user
     * event if they are not incremental (They are uuid themselves).
     *
     * @return string
     */
    public function getFileableUuid()
    {
        return $this['uuid'];
    }

    /**
     * Creation time of the file
     *
     * @return Carbon
     */
    public function getFileableCreatedAt()
    {
        return $this['created_at'];
    }

    /**
     * One of your defined contexts in the config file.
     *
     * @return string
     */
    public function getFileableContext()
    {
        return $this['context'];
    }

    /**
     * Client's given file name.
     *
     * @return string
     */
    public function getFileableOriginalName()
    {
        return $this['original_name'];
    }

    /**
     * Our file name.
     *
     * @return string
     */
    public function getFileableFileName()
    {
        return $this['file_name'];
    }

    /**
     * Used when there are non tracked siblings are added to the file entity (Like resized images), This folder is
     * a unique folder that allows us to perform batch actions on the folder.
     *
     * @return string
     */
    public function getFileableSiblingFolder()
    {
        return $this['sibling_folder'];
    }

    /**
     * Using the same container for all of your application contexts? By defining context for each of them
     * you can control the behaviour of the each category (For example your file manager images are not resized if you need the resize functionality)
     * for something else.
     *
     * @return string
     */
    public function getFileableContextFolder()
    {
        return $this['context_folder'];
    }

    /**
     * Get fileable hash. Is used for detecting repeated files.
     *
     * @return string
     */
    public function getFileableHash()
    {
        return $this['file_hash'];
    }

    /**
     * Ensures that the the two files are really equal by using an additional hash.
     *
     * @return string
     */
    public function getFileableEnsuredHash()
    {
        return $this['file_ensured_hash'];
    }

    /**
     * Get category of the file
     *
     * @return string
     */
    public function getCategory()
    {
        return $this['category'];
    }

    /**
     * @param $query
     * @param $md5
     * @param $sha1
     * @return Builder
     */
    public function scopeHashIs(Builder $query, $md5, $sha1)
    {
        return $query->where(function (Builder $q) use ($md5, $sha1) {
            return $q->where('file_hash', $md5)->where('file_ensured_hash', $sha1);
        });
    }

    /**
     * The time file has been updated.
     *
     * @return Carbon
     */
    public function getFileableUpdatedAt()
    {
        return $this['updated_at'];
    }

    /**
     * @return string|null
     */
    public function getFileableExtension()
    {
        return $this['file_extension'];
    }

    /**
     * Full url.
     *
     * @param array $appending
     * @return string
     */
    public function toUrl($appending = [])
    {
        return UrlGenerator::fromEloquentInstance($this, true, $appending);
    }

    /**
     * Get image templates
     *
     * @param array $appending
     * @return array|null
     */
    public function getImageTemplates($appending = [])
    {
        return UrlGenerator::getImagesTemplatesForEloquentInstance($this, true, $appending);
    }

    /**
     * Get image templates.
     *
     * @return null|array
     */
    public function getImageTemplatesAttribute()
    {
        $imageTemplates = $this->getImageTemplates();

        if ($imageTemplates === null) {
            return null;
        }

        return $imageTemplates['children'];
    }

    /**
     * Get full url.
     *1
     * @return string
     */
    public function getFullUrlAttribute()
    {
        return $this->toUrl();
    }

    public function deleteInstanceAndRemote($onDelete = DeleteTrackedFile::ON_DELETE_DELETE_IF_NOT_IN_OTHERS)
    {
        $command = app(DeleteTrackedFile::class);
        return $command->execute($this, $onDelete);
    }

    public function getTemplate($template, $full = false, $appending = [])
    {
        $templates = $this->getImageTemplates($appending);

        if ($full) {
            return $templates['children'][$template];
        }

        $finalTemplate = $template . '.' . $templates['parent_extension'];

        if (array_key_exists($finalTemplate, $templates['children'])) {
            return $templates['children'][$finalTemplate];
        }

        foreach ($templates['children'] as $t => $address) {
            $name = pathinfo($t, PATHINFO_FILENAME);
            if ($name === $template) {
                return $address;
            }
        }

        throw new \InvalidArgumentException("Template not supported.");
    }

    public function getRealFileSize()
    {
        /** @var FileSecretaryManager $fileSecretaryManager */
        $fileSecretaryManager = app(FileSecretaryManager::class);

        $driver = $fileSecretaryManager->getContextDriver($this->getFileableContext());

        $sibling = $this->getFileableSiblingFolder();

        if ($sibling !== null) {
            $path = $sibling . '/' . $this->getFileableFullFileName();
        } else {
            $path = $this->getFileableFullFileName();
        }

        $path = $this->getFileableContextFolder() . '/' . $path;

        $tries = 3;
        $size = null;

        while ($tries > 0 && $size === null) {
            try {
                $size = $driver->size($path);
            } catch (\Exception $e) {
                $tries--;
                continue;
            }
        }

        return $size;
    }

    public function getFileSize()
    {
        return $this->file_size;
    }

    public function getFileSizeAttribute()
    {
        if (array_get($this->attributes, 'file_size') === null) {
            return null;
        }

        return (int)$this->attributes['file_size'];
    }
}