<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <script>
        
        showAlert()
        async function showAlert(){
            await Swal.fire({
                position: 'center',
                icon: 'success',
                title: 'Verification Completed',                      
            })
            window.close();
        }
    </script>
</body>

</html>