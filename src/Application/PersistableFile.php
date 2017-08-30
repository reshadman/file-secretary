<?php

namespace Reshadman\FileSecretary\Application;

use Carbon\Carbon;

interface PersistableFile
{
    /**
     * The unique identifier think of it as PK
     *
     * @return string
     */
    public function getFileableIdentifier();

    /**
     * Uuid of the file. Exposed to users, as we should not expose PK's or any type of ids to the user
     * event if they are not incremental (They are uuid themselves).
     *
     * @return string
     */
    public function getFileableUuid();

    /**
     * Creation time of the file
     *
     * @return Carbon
     */
    public function getFileableCreatedAt();

    /**
     * The time file has been updated.
     *
     * @return Carbon
     */
    public function getFileableUpdatedAt();

    /**
     * One of your defined contexts in the config file.
     *
     * @return string
     */
    public function getFileableContext();

    /**
     * Client's given file name.
     *
     * @return string
     */
    public function getFileableOriginalName();

    /**
     * Our file name.
     *
     * @return string
     */
    public function getFileableFileName();

    /**
     * Used when there are non tracked siblings are added to the file entity (Like resized images), This folder is
     * a unique folder that allows us to perform batch actions on the folder.
     *
     * @return string
     */
    public function getFileableSiblingFolder();

    /**
     * Using the same container for all of your application contexts? By defining context for each of them
     * you can control the behaviour of the each category (For example your file manager images are not resized if you need the resize functionality)
     * for something else.
     *
     * @return string
     */
    public function getFileableContextFolder();

    /**
     * Get fileable hash. Is used for detecting repeated files.
     *
     * @return string
     */
    public function getFileableHash();

    /**
     * Ensures that the the two files are really equal by using an additional hash.
     *
     * @return string
     */
    public function getFileableEnsuredHash();

    /**
     * Get final path of the file.
     *
     * @return string
     */
    public function getFinalPath();

    /**
     * @return string|null
     */
    public function getFileableExtension();

    /**
     * Get category of the file
     *
     * @return string
     */
    public function getCategory();
}