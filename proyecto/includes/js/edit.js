function habilitar(){
    var chekbox= document.getElementById('cb-47');
 if(chekbox.checked){
    document.getElementById("nombre1").disabled = false;
 }else{
    document.getElementById("nombre1").disabled = true;
 }
}