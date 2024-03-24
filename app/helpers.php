<?php
function customView($blade){        
    
    return view(str_replace('/','',config('constant.prefix')).'.'.$blade);
}
