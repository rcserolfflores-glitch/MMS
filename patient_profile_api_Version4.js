// Replace the local stub with this handler to save to the PHP backend.
// It expects save_patient_details.php and get_patient_details.php to be present.

(function(){
  const formEl = document.getElementById('patientForm');
  if(!formEl) return;
  formEl.addEventListener('submit', async function(e){
    e.preventDefault();
    const submitBtn = formEl.querySelector('.submit-form');
    if(submitBtn) submitBtn.disabled = true;

    const formData = new FormData(formEl);

    try {
      const resp = await fetch('save_patient_details.php', {
        method: 'POST',
        body: formData,
        credentials: 'same-origin'
      });

      const text = await resp.text();
      let data;
      try { data = text ? JSON.parse(text) : {}; } catch(err){
        console.error('Non-JSON response from save_patient_details.php:', text);
        alert('Server returned an unexpected response. Check server logs.');
        return;
      }

      if (!resp.ok) {
        alert('Server error: ' + (data.message || resp.status));
        return;
      }

      if (data.success) {
        alert(data.message || 'Saved successfully');
        // populate UI with returned data (if provided)
        if (data.data) {
          populateProfile(data.data);
        } else {
          // refresh from server
          loadPatientDetails();
        }
        closeFormModal();
      } else {
        alert('Error: ' + (data.message || 'Could not save'));
      }
    } catch (err) {
      console.error('Network error', err);
      alert('Network error: ' + (err && err.message));
    } finally {
      if(submitBtn) submitBtn.disabled = false;
    }
  });
})();