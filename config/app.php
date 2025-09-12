@@ .. @@
         /*
          * Package Service Providers...
          */
+        SimpleSoftwareIO\QrCode\QrCodeServiceProvider::class,
+        Barryvdh\DomPDF\ServiceProvider::class,
        Maatwebsite\Excel\ExcelServiceProvider::class,
 
         /*
          * Application Service Providers...
@@ .. @@
         'URL' => Illuminate\Support\Facades\URL::class,
         'Validator' => Illuminate\Support\Facades\Validator::class,
         'View' => Illuminate\Support\Facades\View::class,
+        'QrCode' => SimpleSoftwareIO\QrCode\Facades\QrCode::class,
+        'PDF' => Barryvdh\DomPDF\Facade\Pdf::class,
        'Excel' => Maatwebsite\Excel\Facades\Excel::class,
 
     ],