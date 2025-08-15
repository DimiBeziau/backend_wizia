import { useState } from "react";
import Stepper from "../components/Stepper";
import Step2SignUp from "../components/Step2SignUp";
import Step1SignUp from "../components/Step1SignUp";
import Step3SignUp from "../components/Step3SignUp";
import Step4SignUp from "../components/Step4SignUp";

function Signup() {
  const [step, setStep] = useState(1);
  const [formData, setFormData] = useState({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
    activity: '',
    logo: '',
    color: '#000000',
    description: '',
  })
  return (
    <div className="w-full min-h-screen flex flex-col justify-start items-center bg-gray-100">
      <img src="Logo-Wizia-1.png" className="max-h-30 my-10" />
      <Stepper step={step} />
      {step === 1 && (
        <Step1SignUp formData={formData} setFormData={setFormData} />
      )}
      {
        step === 2 && (
          <Step2SignUp />
        )
      }
      {
        step === 3 && (
          <Step3SignUp />
        )
      }
      {
        step === 4 && (
          <Step4SignUp />
        )
      }
    </div>
  );
}

export default Signup;