const buyerCheck = document.getElementById('btn-check-4');
const sellerCheck = document.getElementById('btn-check-5');
const addressField = document.getElementById('addressField');
const bankField = document.getElementById('bankField');
const bankNumberField = document.getElementById('bankNumberField');

addressField.style.display = buyerCheck.checked ? 'flex' : 'none';
bankField.style.display = sellerCheck.checked ? 'flex' : 'none';
bankNumberField.style.display = sellerCheck.checked ? 'flex' : 'none';

//Update on every change
buyerCheck.addEventListener('change', function(){
    addressField.style.display = this.checked ? 'flex' : 'none';
});

sellerCheck.addEventListener('change', function(){
    bankField.style.display = this.checked ? 'flex' : 'none';
    bankNumberField.style.display = this.checked ? 'flex' : 'none';
})

function tabSetting(evt, setting){
    //Declare variables
    var iCount, tabcontent, tabLinks;
    console.log("Opening tab: " + setting);
    console.log("Element found: " + document.getElementById(setting));

    //get all elements with class="tabcontent" and hide them
    tabcontent = document.getElementsByClassName("tabcontent");
    for(iCount = 0; iCount < tabcontent.length; iCount++){
        tabcontent[iCount].style.display = "none";
    }

    // Get all elements with tablinks and remove the class "active"
    tabLinks = document.getElementsByClassName("tablinks");
    for(iCount = 0; iCount < tabLinks.length; iCount++){
        tabLinks[iCount].className = tabLinks[iCount].className.replace(" active","");
    }

    //Show the current tab, and add an "active" class to the button that opened the tab
    document.getElementById(setting).style.display = "block";
    evt.currentTarget.className += " active";

}