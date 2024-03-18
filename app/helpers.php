<?php
function customView($blade){        
    return view(str_replace('\\','',env('PREFIX')).'.'.$blade);
}
