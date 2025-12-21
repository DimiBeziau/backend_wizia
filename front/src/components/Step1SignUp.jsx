import { Button, Input } from 'antd';
import { useState } from 'react';
import { Link } from 'react-router-dom';

function Step1SignUp({ formData, setFormData, onNextStep }) {
  function formValidation() {
    if (!formData.first_name || !formData.last_name || !formData.email || !formData.password || !formData.password_confirmation) {
      alert("Veuillez remplir tous les champs obligatoires.");
      return;
    } else if (formData.password !== formData.password_confirmation) {
      alert("Les deux mots de passe ne correspondent pas.");
      return;
    }
    onNextStep();
  }
  return (
    <div className="flex flex-col max-w-[410px] px-5 py-5">
      <p className="pt-5">Pour commencer nos devons connaître ton nom, ton prénom et tes informations de connexion.</p>
      <div className="flex flex-col my-3">
        <label>Prénom *</label>
        <Input variant="filled" placeholder="John" value={formData.first_name} onChange={(ev) => { setFormData((prev) => ({ ...prev, first_name: ev.target.value })) }} />
      </div>
      <div className="flex flex-col my-3">
        <label>Nom *</label>
        <Input variant="filled" placeholder="Doe" value={formData.last_name} onChange={(ev) => { setFormData((prev) => ({ ...prev, last_name: ev.target.value })) }} />
      </div>
      <div className="flex flex-col my-3">
        <label>Email *</label>
        <Input variant="filled" placeholder="exemple@gmail.com" value={formData.email} onChange={(ev) => { setFormData((prev) => ({ ...prev, email: ev.target.value })) }} />
      </div>
      <div className="flex flex-col my-3">
        <label>Téléphone </label>
        <Input variant="filled" placeholder="0606060606" value={formData.phone} onChange={(ev) => { setFormData((prev) => ({ ...prev, phone: ev.target.value })) }} />
      </div>
      <div className="flex flex-col my-3">
        <label>Mot de passe *</label>
        <Input.Password
          variant="filled"
          placeholder="mot de passe"
          value={formData.password}
          onChange={(ev) => { setFormData((prev) => ({ ...prev, password: ev.target.value })) }}
        />
      </div>
      <div className="flex flex-col my-3">
        <label>Confirmation de mot de passe *</label>
        <Input.Password
          variant="filled"
          placeholder="confirmation de mot de passe"
          value={formData.password_confirmation}
          onChange={(ev) => { setFormData((prev) => ({ ...prev, password_confirmation: ev.target.value })) }}
        />
      </div>
      <div className="flex flex-row justify-end my-3">
        <Link to="/login" >J'ai déjà un compte ?</Link>
      </div>
      <div className="flex flex-row justify-end items-center my-3">
        <Button onClick={() => { formValidation() }}>Continuer</Button>
      </div>

    </div>
  );
}

export default Step1SignUp;