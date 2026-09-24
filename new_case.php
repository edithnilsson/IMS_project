<!DOCTYPE html>
<html lang="en">

<head>
    <title>InfecTracker</title>
    <link rel="stylesheet" href="css/cases.css">
</head>
<body>
<div class="img">
    <img src="Pictures/Logo.png" class="logo">
    <img src="Pictures/virus_bl_nobg.png" class="virus_bl">
    <img src="Pictures/virus_tr_nobg.png" class="virus_tr">
    <img src="/Pictures/rings_br_nobg.png" class="rings_br">
</div>

<h1 class="hCase">Register a Case</h1>

<form action method="POST" class="inputs">
    <div class="form-column">

        <div class="form-row">
            <label>
                    Pathogen 
                    <img src="/Pictures/pathogen.icon_bgremoved.png" class="icon">
            </label>

            <div class="input">
                <select id="pathogen">
                    <option value="" disabled selected>Select Pathogen</option>
                    <option value="covid">COVID-19</option>
                    <option value="flu">Influenza</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <label>
                    Date/Time   
                    <img src="/Pictures/calender.icon-bgremoved.png" class="icon">
            </label>

            <div>
                <input type="datetime-local">
            </div>
        </div>

        <div class="form-row">
            <label>
                    Ward     
                    <img src="/Pictures/ward.icon-bgremoved.png" class="icon">
            </label>

            <div>
                <select id="ward">
                    <option value="" disabled selected>Select Ward</option>
                    <option value="icu">ICU</option>
                    <option value="general">General</option>
                </select>
            </div>
        </div>
    </div>
    
    <div class="form-column">

        <div class="form-row">
            <label>
                PatientID <img src="/Pictures/patient.icon-bgremoved.png" class="icon">
            </label>

            <input type="text" id="patient-id" placeholder="Enter Patient ID">
        </div>

        <div>
            <label class="label-inputs">
                    
            </label>
            <div class="comments">
                <input type="text" placeholder="Other Comments...">
            </div>
        </div>

        <button type="submit" class="submit-btn">Submit</button>


    </div>

</form>

</body>