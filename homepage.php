<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>

    <style>
        .button {
            color: black;
            padding: 20px 10px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 30px;
            margin: 6px 4px;
            border-radius: 8px;
            cursor: pointer;
            }
    </style>

</head>
<body>
    <!-- Inserts the logo picture  -->
    <img src="Pictures/Logo.png" alt="Logo" width="300" height="133" >
    
    <!-- Log out button  -->
    <a href="index.php" style="position: absolute; top: 20px; right: 20px; text-decoration: none;">
    <button style="padding: 8px 16px; background-color: #f44336; color: white; border: none; border-radius: 4px; font-weight: bold;">
        Log out
    </button>
    </a>



    
    <div style= "        
        width: 1100px; 
        height: 500px; 
        display: flex;
        gap: 60px;
        align-items: center; 
        padding: 20px 20px;
        box-sizing: border-box;
        margin: 0 auto 20px auto;">

        <!-- Log a case button -->  
        <a href="new_case.php" style="text-decoration: none;">
            <button class="button">
            <b>Log a Case</b><br> 
            Click here</button>
        </a>

        <!-- Placeholder for graph -->     
        <img src="Pictures/Graph_placeholder.png" alt="Logo" width="750" height="453" >


    </div>


<!-- Announcement block -->  
<!-- Once the database is up and running use if and else to choose color, symbol and so on! -->  
<div 
    style= "width: 1100px; margin: 0 auto 20px auto;">
    <h1>Announcements</h1>
     <!--  GENERAL NOTE  -->
    <div 
        style="
        background-color: #88cc00;
        width: 1100px; 
        height: 100px; 
        border-radius: 8px;
        display: flex;
        gap: 30px;
        align-items: center; 
        padding: 20px 20px;
        box-sizing: border-box;">

        <!--  symbol -->
        <span
            style="
            font-size: 70px;
            line-height: 1;" >
            &#9786;
        </span>

        <!--  message -->
        <span> Just wash your hands and you will be fine! </span>
    </div>
    <br>

    <!--  MEDIUM THREAT  -->
    <div 
        style="
        background-color: #ffff00;
        width: 1100px; 
        height: 100px; 
        border-radius: 8px;
        display: flex;
        gap: 30px;
        align-items: center; 
        padding: 20px 20px;
        box-sizing: border-box;">
    
        <!--  symbol -->
        <span
            style="
            font-size: 70px;
            line-height: 1;" >
            &#9888;
        </span>

        <!--  message -->
        <span> Medium threat level!</span>
    </div>
    <br>

    <!--  WARNING! SERIOUS THREAT  -->
    <div 
            style="
            background-color: #ff5050;
            width: 1100px; 
            height: 100px; 
            border-radius: 8px;
            display: flex;
            gap: 30px;
            align-items: center; 
            padding: 20px 20px;
            box-sizing: border-box;">

        <!-- symbol-->
            <span
                style="
                font-size: 120px;
                line-height: 1;" >
                &#9763;
            </span>
            
        <!--  message -->
            <span> Just wash your hands and you will be fine! </span>
    </div>
</div>


</body>
</html>
