<?php
/*Insert data for a new case

For testing type in terminal:
& "C:\MAMP\bin\php\php8.3.1\php.exe" -S localhost:xxxx (ex: 8080, 8000 etc)
Type in browser:
http://localhost:xxxx/new_case.php
*/
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InfecTracker</title>
    <style>
        img {
            display: block;
            margin: auto;
        }

        h1 {
            text-align: center;
        }
    </style>
</head>

<body>
    <img src="placeholder.png" width="600" height="300" class="top-logo">

    <h1>Log a Case</h1>

    <form>
        <label for="pathogen">Pathogen:</label>
        <input list="pathogen-options" id="pathogen" name="pathogen" autocomplete="off">
        <datalist id="pathogen-options">
            <option value="MRSA">
            <option value="SARS-CoV-2">
        </datalist>
    </form>

    
    <form>
        <label for="pathogen">Pathogen:</label>
        <select name="pathogen" id="pathogen" autocomplete="off">
            <option value="" disabled selected>Select Pathogen</option>
            <option value="mrsa">MRSA</option>
            <option value="sars_cov_2">SARS-CoV-2</option>
        </select>
    </form>

</body>

</html>