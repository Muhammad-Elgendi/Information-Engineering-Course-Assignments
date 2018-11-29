function transpostionEncript(){
  if ($("#transpostion")[0].checkValidity()){
    var key = $( "#key1" ).val();
    var plain = $( "#plaintext1" ).val();
    var cipher = $( "#ciphertext1" ).val();
    var request = $.ajax({
                  method: "POST",
                  url: "controller.php",
                  data: { "key": key, "plain": plain ,"cipher": cipher }
                });
    request.done(function( msg ) {
      $( "#ciphertext1" ).val(JSON.stringify(msg));
      // $( "#ciphertext1" ).val(msg);
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
                  url: "controller.php",
                  data: { "key": key, "plain": plain ,"cipher": cipher }
                });
    request.done(function( msg ) {
      $( "#plaintext1" ).val(JSON.stringify(msg));
      // $( "#plaintext1" ).val(msg);

    });
  }
  else
    $("#transpostion")[0].reportValidity();

}