const rangeInput = document.getElementById('range4');
const rangeOutput = document.getElementById('rangeValue');
const quantityValue = document.getElementById('quantityValue');

// initial valye
rangeOutput.textContent = rangeInput.value;
quantityValue.value = rangeInput.value;

rangeInput.addEventListener('input', function(){
    rangeOutput.textContent = this.value;
    quantityValue.value = this.value;
});