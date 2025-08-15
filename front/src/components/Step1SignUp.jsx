import { Input } from 'antd';
import { useState } from 'react';

function Step1SignUp({ formData, setFormData }) {
  return (
    <div className="flex flex-col max-w-[410px] px-5 py-5">
      <p className="py-5">Pour commencer nos devons connaître ton nom, ton prénom et tes informations de connexion.</p>
      <div className="flex flex-col my-3">
        <label>Prénom *</label>
        <Input placeholder="John" value={formData.first_name} onChange={(ev) => { console.log(prev); setFormData((prev) => ({ ...prev, first_name: ev.target.value })) }} />
      </div>
      <div className="flex flex-col my-3">
        <label>Nom *</label>
        <Input placeholder="Doe" value={formData.last_name} onChange={(ev) => { setFormData((prev) => ({ ...prev, last_name: ev.target.value })) }} />
      </div>
      <div className="flex flex-col my-3">
        <label>Email *</label>
        <Input placeholder="exemple@gmail.com" value={formData.email} onChange={(ev) => { setFormData((prev) => ({ ...prev, email: ev.target.value })) }} />
      </div>
      <div className="flex flex-col my-3">
        <label>Téléphone *</label>
        <Input placeholder="0606060606" value={formData.phone} onChange={(ev) => { setFormData((prev) => ({ ...prev, phone: ev.target.value })) }} />
      </div>
      <div className="flex flex-col my-3">
        <label>Mot de passe *</label>
        <Input.Password
          placeholder="mot de passe"
        />
      </div>
      <div className="flex flex-col my-3">
        <label>Confirmation de mot de passe *</label>
        <Input.Password
          placeholder="confirmation de mot de passe"
        />
      </div>

    </div>
  );
}

export default Step1SignUp;