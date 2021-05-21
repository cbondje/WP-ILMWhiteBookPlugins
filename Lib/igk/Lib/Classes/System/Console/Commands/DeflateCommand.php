<?php
namespace IGK\System\Console\Commands;

use IGK\System\Console\AppExecCommand;
use IGK\System\Console\Logger;
use TBN\Models\Logtasktypes;

// class DeflateCommand extends AppExecCommand{
//     var $command = "--deflate";

//     public function exec($command, $file="") {
//         echo "deflating ::: :";
//         $_SERVER['HTTP_ACCEPT_ENCODING'] = 'deflate';
//         if ($file && file_exists($file)){
//             // echo gzdeflate(file_get_contents($file), 5);//, 3);
//             ob_start();
//             echo igk_zip_output("base hello");
//             $c = ob_get_clean();

//             echo gzdecode($c,3);

//             Logger::success("done");
//         }
//      }

    
// }