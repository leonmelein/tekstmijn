$('a[href="#submission"]').tab('show');
$("#inzendingoverschrijven").click(function (e) {
    $("#togglealert").removeClass("show");
    $("#togglealert").addClass("hide");
    $("#alertshow").removeClass("hide");
    $("#alertshow").addClass("show");
});

$("#gaverder").click(function (e) {
    $("#alertshow").removeClass("show");
    $("#alertshow").addClass("hide");
    $("#submissionform").removeClass("hide");
    $("#submissionform").addClass("show");
});