<?php

function checkThreshold($conn, $pathogen_id, $diagnosis_date, $department_id, $hospital_id)
{
    // Get the threshold for this pathogen
    $sql = "SELECT threshold_id, threshold_value, threshold_time_window
            FROM Threshold
            WHERE pathogen_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pathogen_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $threshold = $result->fetch_assoc();

    $stmt->close();

    // No threshold exists for this pathogen
    if (!$threshold) {
        return;
    }

    $threshold_id = $threshold['threshold_id'];
    $threshold_value = $threshold['threshold_value'];
    $threshold_time_window = $threshold['threshold_time_window'];

    // Calculate the beginning of the current threshold window
    $start_date = date(
        'Y-m-d',
        strtotime("-{$threshold_time_window} days")
    );

    $today = date('Y-m-d');

    // The submitted case must itself be within the current window
    if ($diagnosis_date < $start_date || $diagnosis_date > $today) {
        return;
    }

    // Count recent cases of this pathogen
    // within this department and hospital
    $sql = "SELECT COUNT(*) AS case_count
            FROM Infection_case
            WHERE pathogen_id = ?
            AND department_id = ?
            AND hospital_id = ?
            AND case_diagnosis_date BETWEEN ? AND ?";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "iisss",
        $pathogen_id,
        $department_id,
        $hospital_id,
        $start_date,
        $today
    );

    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt->close();

    $case_count = $row['case_count'];

    // Threshold has not been reached
    if ($case_count < $threshold_value) {
        return;
    }

    // Get pathogen name
    $sql = "SELECT pathogen_name
            FROM Pathogen
            WHERE pathogen_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $pathogen_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $pathogen = $result->fetch_assoc();

    $stmt->close();

    $pathogen_name = $pathogen['pathogen_name'];

    // Get department name
    $sql = "SELECT department_name
            FROM Department
            WHERE department_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $department_id);
    $stmt->execute();

    $result = $stmt->get_result();
    $department = $result->fetch_assoc();

    $stmt->close();

    $department_name = $department['department_name'];

    // Create announcement message
    $awarning_message =
        $case_count . " cases of " . $pathogen_name .
        " have been diagnosed in the " .
        $department_name .
        " ward within the last " .
        $threshold_time_window . " days.";

    // Check if an announcement already exists for this
    // threshold, department and hospital during the current window
    $sql = "SELECT awarning_id, awarning_status
            FROM Automatic_warning
            WHERE threshold_id = ?
            AND department_id = ?
            AND hospital_id = ?
            AND awarning_date >= ?
            LIMIT 1";

    $stmt = $conn->prepare($sql);

    $start_datetime = $start_date . " 00:00:00";

    $stmt->bind_param(
        "iiss",
        $threshold_id,
        $department_id,
        $hospital_id,
        $start_datetime
    );

    $stmt->execute();

    $result = $stmt->get_result();
    $existing_warning = $result->fetch_assoc();

    $stmt->close();

    // An announcement already exists in this window
    if ($existing_warning) {

        // Update it while it is still active
        if ($existing_warning['awarning_status'] === 'active') {

            $awarning_id = $existing_warning['awarning_id'];

            $sql = "UPDATE Automatic_warning
                    SET awarning_message = ?
                    WHERE awarning_id = ?";

            $stmt = $conn->prepare($sql);

            $stmt->bind_param(
                "ss",
                $awarning_message,
                $awarning_id
            );

            $stmt->execute();
            $stmt->close();
        }

        return;
    }

    $awarning_status = "active";

    // Create the automatic announcement
    $sql = "INSERT INTO Automatic_warning
            (awarning_id, threshold_id, department_id,
             awarning_message, awarning_status, hospital_id)
            VALUES (UUID(), ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    $stmt->bind_param(
        "iisss",
        $threshold_id,
        $department_id,
        $awarning_message,
        $awarning_status,
        $hospital_id
    );

    $stmt->execute();

    $stmt->close();
}
?>
