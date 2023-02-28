<?php

namespace Webkul\Bulkupload\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Http\JsonResponse;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Webkul\Bulkupload\Repositories\Products\ConfigurableProductRepository;

class ImportPortionOfCSV implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $request;
    public $product;
    public $imageZipName;
    public $data_flow_profile_id;
    public $totalNumberOfCSVRecord;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($request, $imageZipName, &$product, $data_flow_profile_id, $totalNumberOfCSVRecord)
    {
        $this->request = $request;
        $this->product = $product;
        $this->imageZipName = $imageZipName;
        $this->data_flow_profile_id = $data_flow_profile_id;
        $this->totalNumberOfCSVRecord = $totalNumberOfCSVRecord;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ConfigurableProductRepository $configurableProductRepository)
    {
        $configurableProduct = $configurableProductRepository->createProduct($this->request, $this->imageZipName, $this->product);

        $customRequest = [
            'data_flow_profile_id' => $this->data_flow_profile_id,
            'numberOfCSVRecord' => $configurableProduct['remainDataInCSV'],
            'countOfStartedProfiles' => $configurableProduct['countOfStartedProfiles'],
            'totalNumberOfCSVRecord' => $this->totalNumberOfCSVRecord,
            'productUploaded' => $configurableProduct['productsUploaded'],
            'errorCount' => 0,
        ];

        app('Webkul\Bulkupload\Http\Controllers\Admin\HelperController')->runProfile(customRequest: $customRequest);
    }
}
