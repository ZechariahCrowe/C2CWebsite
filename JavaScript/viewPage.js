function tabSetting(evt, setting){
    //Declare variables
    var iCount, tabcontent, tabLinks;

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