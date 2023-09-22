<?php $__env->startSection('content'); ?>
<div class="jumbotron">
    <p class="lead">Events</p>
    <?php if(!empty($eventsData)): ?>
        <h4>Welcome to the Events Page!</h4>
        <form id="eventForm">
            <?php echo csrf_field(); ?>
            <div class="form-group">
                <label for="subject">Subject:</label>
                <input type="text" id="subject" name="subject" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="location">Location:</label>
                <input type="text" id="location" name="location" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="start">Start Date and Time:</label>
                <input type="datetime-local" id="start" name="start" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="end">End Date and Time:</label>
                <input type="datetime-local" id="end" name="end" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="timezone">Timezone:</label>
                <input type="text" id="timezone" name="timezone" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="content">Content:</label>
                <textarea id="content" name="content" class="form-control" required></textarea>
            </div>
            <div class="form-group">
                <label for="attendeesRequired">Attendees (Required):</label>
                <input type="text" id="attendeesRequired" name="attendeesRequired" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="attendeesOptional">Attendees (Optional):</label>
                <input type="text" id="attendeesOptional" name="attendeesOptional" class="form-control">
            </div>
            <?php if($editEventId === 0): ?>
            <button type="button" id="createEvent" class="btn btn-primary">Submit Event</button>
            <button type="button" onclick="GetEvent()" class="btn btn-secondary">Refresh</button>
            <?php endif; ?>
        </form>
        
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Subject</th>
                    <th>Start</th>
                    <th>End</th>
                    <th>Location</th>
                    <th>Edit</th>
                    <th>Cancel</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $eventsData; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $event): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($event['title']); ?></td>
                        <td><?php echo e($event['start']); ?></td>
                        <td><?php echo e($event['end']); ?></td>
                        <td><?php echo e($event['location']); ?></td>
                        <td><button onclick="HandleEdit(<?php echo e(json_encode($event)); ?>)">edit</button></td>
                        <td><button class="cancel-button" onclick="CancelEvent('<?php echo e($event['event_id']); ?>')">cancel</button></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No events data available.</p>
    <?php endif; ?>
</div>
<script>

const accessToken = "<?php echo e($event['access_token']); ?>";
console.log(accessToken,"accessToken");

let eventsData = <?php echo json_encode($eventsData, 15, 512) ?>; 
let editEventId = <?php echo e($editEventId); ?>;
console.log("eventsData",eventsData,editEventId);



document.addEventListener("DOMContentLoaded", function () {
    const createEventButton = document.getElementById("createEvent");
    const tableBody = document.querySelector("tbody"); 
    
 
    createEventButton.addEventListener("click", function () {
    const eventForm = document.getElementById("eventForm");
    const formData = new FormData(eventForm);

    const eventPayload = {
        "accesstoken": accessToken,
        "subject": formData.get("subject"),
        "start": formData.get("start"),
        "end": formData.get("end"),
        "timezone": formData.get("timezone"),
        "location": formData.get("location"),
        "content": formData.get("content"),
        "attendeesRequired": [formData.get("attendeesRequired")],
        "attendeesOptional": [formData.get("attendeesOptional")],
    };
if(editEventId===0){
    fetch("/add-event", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify(eventPayload),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            eventsData.push(data.event); // Assuming the server responds with the created event
           
        } else {
            console.error("Event creation failed:", data.error);
        }
    })
    .catch(error => {
        console.error("Error:", error);
    });
}else{
    fetch("/update-event", {
        method: "PATCH",
        headers: {
            "Content-Type": "application/json",
        },
        body: JSON.stringify({...eventPayload,accesstoken:accessToken,eventId:editEventId}),
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            eventsData.push(data.event); // Assuming the server responds with the created event
           
        } else {
            console.error("Event creation failed:", data.error);
        }
    })
    .catch(error => {
        console.error("Error:", error);
    });
    editEventId=0;
}
    
});

});

function GetEvent(data){
    const jsonObject = {
            "access_token": accessToken
        };
        fetch("/get-event", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                // Add other headers if needed, e.g., authorization headers
            },
            body: JSON.stringify(jsonObject),
        })
        .then(response => response.json())
        .then(data => {
            eventsData = data;
            // return  view('events', ['eventsData' => eventsData]);
        })
        .catch(error => {
            // Handle errors, e.g., display an error message
        });
}

function CancelEvent(data){
    fetch("/cancel-event", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ eventId: data ,accesstoken:accessToken}),
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    eventsData = eventsData.filter(event => event.event_id !== eventId);

               
                } else {
                    console.error("Cancellation failed:", data.error);
                }
            })
            .catch(error => {
                console.error("Error:", error);
            });
}
function HandleEdit(data){
    editEventId=data.event_id;

    console.log("editable",data);
    const formattedEnd = formatDateForInput(data.end); 
    const formattedStart = formatDateForInput(data.start); 

   
    document.getElementById("subject").value = data.title;
    document.getElementById("location").value = data.location;
    document.getElementById("start").value = formattedStart;
    document.getElementById("end").value = formattedEnd;
    document.getElementById("timezone").value = data.timezone;
    document.getElementById("content").value = data.content;
    document.getElementById("attendeesRequired").value = data.attendees_required;
    document.getElementById("attendeesOptional").value = data.attendees_optional;
   
}
function formatDateForInput(datetime) {
    const date = new Date(datetime);
    const formattedDate = date.toISOString().slice(0, 16); // Format as 'YYYY-MM-DDTHH:mm'
    return formattedDate;
}
function clearFormFields() {
    // Clear all form fields
    document.getElementById("subject").value = "";
    document.getElementById("location").value = "";
    document.getElementById("start").value = "";
    document.getElementById("end").value = "";
    document.getElementById("timezone").value = "";
    document.getElementById("content").value = "";
    document.getElementById("attendeesRequired").value = "";
    document.getElementById("attendeesOptional").value = "";
}

</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layout', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\HP\Downloads\HH-outlook-main\HH-outlook-main\resources\views/events.blade.php ENDPATH**/ ?>