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
            <link rel="stylesheet" href="css/case.css">

</head>

<!-- Main Content Area -->
    <main>
        <img src="Pictures\Logo.png" width="600" height="300" class="top-logo">

        <h1>Log a Case</h1>

        <form class="form-grid">
            
            <!-- Row 1: Pathogen & Patient ID -->
            <label class="form-label" for="pathogen">
                Pathogen 
                <span class="icon-placeholder">🦠</span> <!-- Replace with icon image -->
            </label>
            <div class="select-wrapper">
                <select id="pathogen" class="input-green">
                    <option value="" disabled selected>Select Pathogen</option>
                    <option value="covid">COVID-19</option>
                    <option value="flu">Influenza</option>
                </select>
            </div>

            <label class="form-label" for="patient-id">
                Patient ID 
                <span class="icon-placeholder">👤</span> <!-- Replace with icon image -->
            </label>
            <input type="text" id="patient-id" class="input-blue" placeholder="Enter Patient ID">

            <!-- Row 2: Date & Ward -->
            <label class="form-label">
                Date 
                <span class="icon-placeholder">📅</span> <!-- Replace with icon image -->
            </label>
            <div class="date-group">
                <input type="text" class="input-green day" value="17" aria-label="Day">
                <input type="text" class="input-green month" value="October" aria-label="Month">
                <input type="text" class="input-green year" value="2026" aria-label="Year">
            </div>

            <label class="form-label" for="ward">
                Ward 
                <span class="icon-placeholder">🏥</span> <!-- Replace with icon image -->
            </label>
            <div class="select-wrapper">
                <select id="ward" class="input-green">
                    <option value="" disabled selected>Select Ward</option>
                    <option value="icu">ICU</option>
                    <option value="general">General</option>
                </select>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="submit-btn">Submit</button>
        </form>
    </main>

</body>
</html>

</html>