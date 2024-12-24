$url = "https://github.com/danhhong/Battambang/raw/master/Battambang-Regular.ttf"
$output = "Battambang-Regular.ttf"
Invoke-WebRequest -Uri $url -OutFile $output
