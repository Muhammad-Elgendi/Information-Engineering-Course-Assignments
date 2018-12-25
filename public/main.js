$(document).ready(function(){
  // Add smooth scrolling to all links in navbar + footer link
  $(".navbar a, footer a[href='#myPage']").on('click', function(event) {
    // Make sure this.hash has a value before overriding default behavior
    if (this.hash !== "") {
      // Prevent default anchor click behavior
      event.preventDefault();

      // Store hash
      var hash = this.hash;

      // Using jQuery's animate() method to add smooth page scroll
      // The optional number (900) specifies the number of milliseconds it takes to scroll to the specified area
      $('html, body').animate({
        scrollTop: $(hash).offset().top
      }, 900, function(){
   
        // Add hash (#) to URL when done scrolling (default click behavior)
        window.location.hash = hash;
      });
    } // End if
  });
  
  $(window).scroll(function() {
    $(".slideanim").each(function(){
      var pos = $(this).offset().top;

      var winTop = $(window).scrollTop();
        if (pos < winTop + 600) {
          $(this).addClass("slide");
        }
    });
  });
})

function transpostionEncript(){
  if ($("#transpostion")[0].checkValidity()){
    var key = $( "#key1" ).val();
    var plain = $( "#plaintext1" ).val();
    var cipher = $( "#ciphertext1" ).val();
    var request = $.ajax({
                  method: "POST",
                  url: "transpostionController.php",
                  data: { "key": key, "plain": plain ,"cipher": cipher }
                });
    request.done(function( msg ) {
      // $( "#ciphertext1" ).val(JSON.stringify(msg));
      // alert (JSON.stringify(msg));
      $( "#ciphertext1" ).val(msg);
    });
  }
  else
    $("#transpostion")[0].reportValidity();

}

function transpostionDecript(){
  if ($("#transpostion")[0].checkValidity()){
    var key = $( "#key1" ).val();
    var plain = $( "#plaintext1" ).val();
    var cipher = $( "#ciphertext1" ).val();
    var request = $.ajax({
                  method: "POST",
                  url: "transpostionController.php",
                  data: { "key": key, "plain": plain ,"cipher": cipher }
                });
    request.done(function( msg ) {
      $( "#plaintext1" ).val(msg);
      // $( "#plaintext1" ).val(msg);

    });
  }
  else
    $("#transpostion")[0].reportValidity();

}

function desEncript(){
  if ($("#des")[0].checkValidity()){
    var key = $( "#key2" ).val();
    var plain = $( "#plaintext2" ).val();
    var cipher = $( "#ciphertext2" ).val();
    var request = $.ajax({
                  method: "POST",
                  url: "desController.php",
                  data: { "key": key, "plain": plain ,"cipher": cipher }
                });
    request.done(function( msg ) {
      // $( "#ciphertext2" ).val(JSON.stringify(msg));
      // alert (JSON.stringify(msg));
      $( "#ciphertext2" ).val(msg);
    });
  }
  else
    $("#des")[0].reportValidity();

}