<template id="deletionAlertTemplate" data-deletion-template>
    <div class="row" id="deletePrompt">
        <div class="container">
            <button type="button" class="btn btn-danger float-left" onclick="swal.setActionValue({ confirm: 'temporary' }); swal.close();">Delete</button>
            <button type="button" class="btn btn-outline-danger float-right" onclick="$('#deletePrompt').hide(); $('#permanent-implication').show()">Completely Delete</button>
        </div>
    </div>
    <div class="row" id="permanent-implication" style="display: none; text-align: left">
        <p>{{ $permanentDeletionWarning }}</p>
        <p>This action is NOT reversible. Do you agree?</p>
        <div class="container">
            <a href="javascript:void(0);" onclick="$('#permanent-implication').hide(); $('#deletePrompt').show();" class="float-left text-bold-500">No, Go back</a>
            <a href="javascript:void(0);" class="float-right text-danger text-bold-500" onclick="swal.setActionValue({ confirm: 'permanent' }); swal.close();">Yes, Continue</a>
        </div>
    </div>
</template>
