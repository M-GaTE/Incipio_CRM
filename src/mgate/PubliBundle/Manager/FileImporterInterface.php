<?php

namespace mgate\PubliBundle\Manager;

use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Interface ImporterInterface
 * @package mgate\PubliBundle\Manager
 * Interface for all services which imports data from an external source.
 */
interface FileImporterInterface
{
    /**
     * @return array  an array containing expected fields in form. Controller will iterate
     * that array to display it as a table in form view.
     */
    public function expectedFormat();

    /**
     * @param UploadedFile $upload resources file contzaining data to import.
     * @return mixed
     * Process Import.
     */
    public function run(UploadedFile $upload);


}