<?php

$worker = new Deno\Runtime\MainWorker();

$worker->execute_main_module();
// $worker->run_event_loop();


