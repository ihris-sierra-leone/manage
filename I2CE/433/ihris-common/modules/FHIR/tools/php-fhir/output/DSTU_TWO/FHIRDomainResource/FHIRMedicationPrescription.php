<?php namespace DSTU_TWO\FHIRDomainResource;

/*!
 * This class was generated with the PHPFHIR library (https://github.com/dcarbone/php-fhir) using
 * class definitions from HL7 FHIR (https://www.hl7.org/fhir/)
 * 
 * Class creation date: May 13th, 2016
 * 
 * PHPFHIR Copyright:
 * 
 * Copyright 2016 Daniel Carbone (daniel.p.carbone@gmail.com)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *        http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 *
 * FHIR Copyright Notice:
 *
 *   Copyright (c) 2011+, HL7, Inc.
 *   All rights reserved.
 * 
 *   Redistribution and use in source and binary forms, with or without modification,
 *   are permitted provided that the following conditions are met:
 * 
 *    * Redistributions of source code must retain the above copyright notice, this
 *      list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright notice,
 *      this list of conditions and the following disclaimer in the documentation
 *      and/or other materials provided with the distribution.
 *    * Neither the name of HL7 nor the names of its contributors may be used to
 *      endorse or promote products derived from this software without specific
 *      prior written permission.
 * 
 *   THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 *   ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 *   WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 *   IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT,
 *   INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT
 *   NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 *   PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 *   WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *   ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *   POSSIBILITY OF SUCH DAMAGE.
 * 
 * 
 *   Generated on Sat, Oct 24, 2015 07:41+1100 for FHIR v1.0.2
 * 
 *   Note: the schemas & schematrons do not contain all of the rules about what makes resources
 *   valid. Implementers will still need to be familiar with the content of the specification and with
 *   any profiles that apply to the resources in order to make a conformant implementation.
 * 
 */

use DSTU_TWO\FHIRResource\FHIRDomainResource;
use DSTU_TWO\JsonSerializable;

/**
 * An order for both supply of the medication and the instructions for administration of the medication to a patient.
 * If the element is present, it must have either a @value, an @id, or extensions
 */
class FHIRMedicationPrescription extends FHIRDomainResource implements JsonSerializable
{
    /**
     * External identifier - one that would be used by another non-FHIR system - for example a re-imbursement system might issue its own id for each prescription that is created.  This is particularly important where FHIR only provides part of an erntire workflow process where records have to be tracked through an entire system.
     * @var \DSTU_TWO\FHIRElement\FHIRIdentifier[]
     */
    public $identifier = array();

    /**
     * The date (and perhaps time) when the prescription was written.
     * @var \DSTU_TWO\FHIRElement\FHIRDateTime
     */
    public $dateWritten = null;

    /**
     * A code specifying the state of the order.  Generally this will be active or completed state.
     * @var \DSTU_TWO\FHIRElement\FHIRMedicationPrescriptionStatus
     */
    public $status = null;

    /**
     * The date (and perhaps time) when the prescription was stopped.
     * @var \DSTU_TWO\FHIRElement\FHIRDateTime
     */
    public $dateEnded = null;

    /**
     * The reason why the prescription was stopped, if it was.
     * @var \DSTU_TWO\FHIRElement\FHIRCodeableConcept
     */
    public $reasonEnded = null;

    /**
     * A link to a resource representing the person to whom the medication will be given.
     * @var \DSTU_TWO\FHIRElement\FHIRReference
     */
    public $patient = null;

    /**
     * The healthcare professional responsible for authorizing the prescription.
     * @var \DSTU_TWO\FHIRElement\FHIRReference
     */
    public $prescriber = null;

    /**
     * A link to a resource that identifies the particular occurrence of contact between patient and health care provider.
     * @var \DSTU_TWO\FHIRElement\FHIRReference
     */
    public $encounter = null;

    /**
     * Can be the reason or the indication for writing the prescription. (choose any one of reason*, but only one)
     * @var \DSTU_TWO\FHIRElement\FHIRCodeableConcept
     */
    public $reasonCodeableConcept = null;

    /**
     * Can be the reason or the indication for writing the prescription. (choose any one of reason*, but only one)
     * @var \DSTU_TWO\FHIRElement\FHIRReference
     */
    public $reasonReference = null;

    /**
     * Extra information about the prescription that could not be conveyed by the other attributes.
     * @var \DSTU_TWO\FHIRElement\FHIRString
     */
    public $note = null;

    /**
     * Identifies the medication being administered. This is a link to a resource that represents the medication which may be the details of the medication or simply an attribute carrying a code that identifies the medication from a known list of medications. (choose any one of medication*, but only one)
     * @var \DSTU_TWO\FHIRElement\FHIRCodeableConcept
     */
    public $medicationCodeableConcept = null;

    /**
     * Identifies the medication being administered. This is a link to a resource that represents the medication which may be the details of the medication or simply an attribute carrying a code that identifies the medication from a known list of medications. (choose any one of medication*, but only one)
     * @var \DSTU_TWO\FHIRElement\FHIRReference
     */
    public $medicationReference = null;

    /**
     * Indicates how the medication is to be used by the patient.
     * @var \DSTU_TWO\FHIRResource\FHIRMedicationPrescription\FHIRMedicationPrescriptionDosageInstruction[]
     */
    public $dosageInstruction = array();

    /**
     * Deals with details of the dispense part of the order.
     * @var \DSTU_TWO\FHIRResource\FHIRMedicationPrescription\FHIRMedicationPrescriptionDispense
     */
    public $dispense = null;

    /**
     * Indicates whether or not substitution can or should be part of the dispense. In some cases substitution must happen, in other cases substitution must not happen, and in others it does not matter. This block explains the prescriber's intent. If nothing is specified substitution may be done.
     * @var \DSTU_TWO\FHIRResource\FHIRMedicationPrescription\FHIRMedicationPrescriptionSubstitution
     */
    public $substitution = null;

    /**
     * A link to a resource representing an earlier order or prescription that this order supersedes.
     * @var \DSTU_TWO\FHIRElement\FHIRReference
     */
    public $priorPrescription = null;

    /**
     * @var string
     */
    private $_fhirElementName = 'MedicationPrescription';

    /**
     * External identifier - one that would be used by another non-FHIR system - for example a re-imbursement system might issue its own id for each prescription that is created.  This is particularly important where FHIR only provides part of an erntire workflow process where records have to be tracked through an entire system.
     * @return \DSTU_TWO\FHIRElement\FHIRIdentifier[]
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * External identifier - one that would be used by another non-FHIR system - for example a re-imbursement system might issue its own id for each prescription that is created.  This is particularly important where FHIR only provides part of an erntire workflow process where records have to be tracked through an entire system.
     * @param \DSTU_TWO\FHIRElement\FHIRIdentifier[] $identifier
     * @return $this
     */
    public function addIdentifier($identifier)
    {
        $this->identifier[] = $identifier;
        return $this;
    }

    /**
     * The date (and perhaps time) when the prescription was written.
     * @return \DSTU_TWO\FHIRElement\FHIRDateTime
     */
    public function getDateWritten()
    {
        return $this->dateWritten;
    }

    /**
     * The date (and perhaps time) when the prescription was written.
     * @param \DSTU_TWO\FHIRElement\FHIRDateTime $dateWritten
     * @return $this
     */
    public function setDateWritten($dateWritten)
    {
        $this->dateWritten = $dateWritten;
        return $this;
    }

    /**
     * A code specifying the state of the order.  Generally this will be active or completed state.
     * @return \DSTU_TWO\FHIRElement\FHIRMedicationPrescriptionStatus
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * A code specifying the state of the order.  Generally this will be active or completed state.
     * @param \DSTU_TWO\FHIRElement\FHIRMedicationPrescriptionStatus $status
     * @return $this
     */
    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    /**
     * The date (and perhaps time) when the prescription was stopped.
     * @return \DSTU_TWO\FHIRElement\FHIRDateTime
     */
    public function getDateEnded()
    {
        return $this->dateEnded;
    }

    /**
     * The date (and perhaps time) when the prescription was stopped.
     * @param \DSTU_TWO\FHIRElement\FHIRDateTime $dateEnded
     * @return $this
     */
    public function setDateEnded($dateEnded)
    {
        $this->dateEnded = $dateEnded;
        return $this;
    }

    /**
     * The reason why the prescription was stopped, if it was.
     * @return \DSTU_TWO\FHIRElement\FHIRCodeableConcept
     */
    public function getReasonEnded()
    {
        return $this->reasonEnded;
    }

    /**
     * The reason why the prescription was stopped, if it was.
     * @param \DSTU_TWO\FHIRElement\FHIRCodeableConcept $reasonEnded
     * @return $this
     */
    public function setReasonEnded($reasonEnded)
    {
        $this->reasonEnded = $reasonEnded;
        return $this;
    }

    /**
     * A link to a resource representing the person to whom the medication will be given.
     * @return \DSTU_TWO\FHIRElement\FHIRReference
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * A link to a resource representing the person to whom the medication will be given.
     * @param \DSTU_TWO\FHIRElement\FHIRReference $patient
     * @return $this
     */
    public function setPatient($patient)
    {
        $this->patient = $patient;
        return $this;
    }

    /**
     * The healthcare professional responsible for authorizing the prescription.
     * @return \DSTU_TWO\FHIRElement\FHIRReference
     */
    public function getPrescriber()
    {
        return $this->prescriber;
    }

    /**
     * The healthcare professional responsible for authorizing the prescription.
     * @param \DSTU_TWO\FHIRElement\FHIRReference $prescriber
     * @return $this
     */
    public function setPrescriber($prescriber)
    {
        $this->prescriber = $prescriber;
        return $this;
    }

    /**
     * A link to a resource that identifies the particular occurrence of contact between patient and health care provider.
     * @return \DSTU_TWO\FHIRElement\FHIRReference
     */
    public function getEncounter()
    {
        return $this->encounter;
    }

    /**
     * A link to a resource that identifies the particular occurrence of contact between patient and health care provider.
     * @param \DSTU_TWO\FHIRElement\FHIRReference $encounter
     * @return $this
     */
    public function setEncounter($encounter)
    {
        $this->encounter = $encounter;
        return $this;
    }

    /**
     * Can be the reason or the indication for writing the prescription. (choose any one of reason*, but only one)
     * @return \DSTU_TWO\FHIRElement\FHIRCodeableConcept
     */
    public function getReasonCodeableConcept()
    {
        return $this->reasonCodeableConcept;
    }

    /**
     * Can be the reason or the indication for writing the prescription. (choose any one of reason*, but only one)
     * @param \DSTU_TWO\FHIRElement\FHIRCodeableConcept $reasonCodeableConcept
     * @return $this
     */
    public function setReasonCodeableConcept($reasonCodeableConcept)
    {
        $this->reasonCodeableConcept = $reasonCodeableConcept;
        return $this;
    }

    /**
     * Can be the reason or the indication for writing the prescription. (choose any one of reason*, but only one)
     * @return \DSTU_TWO\FHIRElement\FHIRReference
     */
    public function getReasonReference()
    {
        return $this->reasonReference;
    }

    /**
     * Can be the reason or the indication for writing the prescription. (choose any one of reason*, but only one)
     * @param \DSTU_TWO\FHIRElement\FHIRReference $reasonReference
     * @return $this
     */
    public function setReasonReference($reasonReference)
    {
        $this->reasonReference = $reasonReference;
        return $this;
    }

    /**
     * Extra information about the prescription that could not be conveyed by the other attributes.
     * @return \DSTU_TWO\FHIRElement\FHIRString
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Extra information about the prescription that could not be conveyed by the other attributes.
     * @param \DSTU_TWO\FHIRElement\FHIRString $note
     * @return $this
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * Identifies the medication being administered. This is a link to a resource that represents the medication which may be the details of the medication or simply an attribute carrying a code that identifies the medication from a known list of medications. (choose any one of medication*, but only one)
     * @return \DSTU_TWO\FHIRElement\FHIRCodeableConcept
     */
    public function getMedicationCodeableConcept()
    {
        return $this->medicationCodeableConcept;
    }

    /**
     * Identifies the medication being administered. This is a link to a resource that represents the medication which may be the details of the medication or simply an attribute carrying a code that identifies the medication from a known list of medications. (choose any one of medication*, but only one)
     * @param \DSTU_TWO\FHIRElement\FHIRCodeableConcept $medicationCodeableConcept
     * @return $this
     */
    public function setMedicationCodeableConcept($medicationCodeableConcept)
    {
        $this->medicationCodeableConcept = $medicationCodeableConcept;
        return $this;
    }

    /**
     * Identifies the medication being administered. This is a link to a resource that represents the medication which may be the details of the medication or simply an attribute carrying a code that identifies the medication from a known list of medications. (choose any one of medication*, but only one)
     * @return \DSTU_TWO\FHIRElement\FHIRReference
     */
    public function getMedicationReference()
    {
        return $this->medicationReference;
    }

    /**
     * Identifies the medication being administered. This is a link to a resource that represents the medication which may be the details of the medication or simply an attribute carrying a code that identifies the medication from a known list of medications. (choose any one of medication*, but only one)
     * @param \DSTU_TWO\FHIRElement\FHIRReference $medicationReference
     * @return $this
     */
    public function setMedicationReference($medicationReference)
    {
        $this->medicationReference = $medicationReference;
        return $this;
    }

    /**
     * Indicates how the medication is to be used by the patient.
     * @return \DSTU_TWO\FHIRResource\FHIRMedicationPrescription\FHIRMedicationPrescriptionDosageInstruction[]
     */
    public function getDosageInstruction()
    {
        return $this->dosageInstruction;
    }

    /**
     * Indicates how the medication is to be used by the patient.
     * @param \DSTU_TWO\FHIRResource\FHIRMedicationPrescription\FHIRMedicationPrescriptionDosageInstruction[] $dosageInstruction
     * @return $this
     */
    public function addDosageInstruction($dosageInstruction)
    {
        $this->dosageInstruction[] = $dosageInstruction;
        return $this;
    }

    /**
     * Deals with details of the dispense part of the order.
     * @return \DSTU_TWO\FHIRResource\FHIRMedicationPrescription\FHIRMedicationPrescriptionDispense
     */
    public function getDispense()
    {
        return $this->dispense;
    }

    /**
     * Deals with details of the dispense part of the order.
     * @param \DSTU_TWO\FHIRResource\FHIRMedicationPrescription\FHIRMedicationPrescriptionDispense $dispense
     * @return $this
     */
    public function setDispense($dispense)
    {
        $this->dispense = $dispense;
        return $this;
    }

    /**
     * Indicates whether or not substitution can or should be part of the dispense. In some cases substitution must happen, in other cases substitution must not happen, and in others it does not matter. This block explains the prescriber's intent. If nothing is specified substitution may be done.
     * @return \DSTU_TWO\FHIRResource\FHIRMedicationPrescription\FHIRMedicationPrescriptionSubstitution
     */
    public function getSubstitution()
    {
        return $this->substitution;
    }

    /**
     * Indicates whether or not substitution can or should be part of the dispense. In some cases substitution must happen, in other cases substitution must not happen, and in others it does not matter. This block explains the prescriber's intent. If nothing is specified substitution may be done.
     * @param \DSTU_TWO\FHIRResource\FHIRMedicationPrescription\FHIRMedicationPrescriptionSubstitution $substitution
     * @return $this
     */
    public function setSubstitution($substitution)
    {
        $this->substitution = $substitution;
        return $this;
    }

    /**
     * A link to a resource representing an earlier order or prescription that this order supersedes.
     * @return \DSTU_TWO\FHIRElement\FHIRReference
     */
    public function getPriorPrescription()
    {
        return $this->priorPrescription;
    }

    /**
     * A link to a resource representing an earlier order or prescription that this order supersedes.
     * @param \DSTU_TWO\FHIRElement\FHIRReference $priorPrescription
     * @return $this
     */
    public function setPriorPrescription($priorPrescription)
    {
        $this->priorPrescription = $priorPrescription;
        return $this;
    }

    /**
     * @return string
     */
    public function get_fhirElementName()
    {
        return $this->_fhirElementName;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->get_fhirElementName();
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        $json = parent::jsonSerialize();
        $json['resourceType'] = $this->_fhirElementName;
        if (0 < count($this->identifier)) {
            $json['identifier'] = array();
            foreach($this->identifier as $identifier) {
                $json['identifier'][] = $identifier->jsonSerialize();
            }
        }
        if (null !== $this->dateWritten) $json['dateWritten'] = $this->dateWritten->jsonSerialize();
        if (null !== $this->status) $json['status'] = $this->status->jsonSerialize();
        if (null !== $this->dateEnded) $json['dateEnded'] = $this->dateEnded->jsonSerialize();
        if (null !== $this->reasonEnded) $json['reasonEnded'] = $this->reasonEnded->jsonSerialize();
        if (null !== $this->patient) $json['patient'] = $this->patient->jsonSerialize();
        if (null !== $this->prescriber) $json['prescriber'] = $this->prescriber->jsonSerialize();
        if (null !== $this->encounter) $json['encounter'] = $this->encounter->jsonSerialize();
        if (null !== $this->reasonCodeableConcept) $json['reasonCodeableConcept'] = $this->reasonCodeableConcept->jsonSerialize();
        if (null !== $this->reasonReference) $json['reasonReference'] = $this->reasonReference->jsonSerialize();
        if (null !== $this->note) $json['note'] = $this->note->jsonSerialize();
        if (null !== $this->medicationCodeableConcept) $json['medicationCodeableConcept'] = $this->medicationCodeableConcept->jsonSerialize();
        if (null !== $this->medicationReference) $json['medicationReference'] = $this->medicationReference->jsonSerialize();
        if (0 < count($this->dosageInstruction)) {
            $json['dosageInstruction'] = array();
            foreach($this->dosageInstruction as $dosageInstruction) {
                $json['dosageInstruction'][] = $dosageInstruction->jsonSerialize();
            }
        }
        if (null !== $this->dispense) $json['dispense'] = $this->dispense->jsonSerialize();
        if (null !== $this->substitution) $json['substitution'] = $this->substitution->jsonSerialize();
        if (null !== $this->priorPrescription) $json['priorPrescription'] = $this->priorPrescription->jsonSerialize();
        return $json;
    }

    /**
     * @param boolean $returnSXE
     * @param \SimpleXMLElement $sxe
     * @return string|\SimpleXMLElement
     */
    public function xmlSerialize($returnSXE = false, $sxe = null)
    {
        if (null === $sxe) $sxe = new \SimpleXMLElement('<MedicationPrescription xmlns="http://hl7.org/fhir"></MedicationPrescription>');
        parent::xmlSerialize(true, $sxe);
        if (0 < count($this->identifier)) {
            foreach($this->identifier as $identifier) {
                $identifier->xmlSerialize(true, $sxe->addChild('identifier'));
            }
        }
        if (null !== $this->dateWritten) $this->dateWritten->xmlSerialize(true, $sxe->addChild('dateWritten'));
        if (null !== $this->status) $this->status->xmlSerialize(true, $sxe->addChild('status'));
        if (null !== $this->dateEnded) $this->dateEnded->xmlSerialize(true, $sxe->addChild('dateEnded'));
        if (null !== $this->reasonEnded) $this->reasonEnded->xmlSerialize(true, $sxe->addChild('reasonEnded'));
        if (null !== $this->patient) $this->patient->xmlSerialize(true, $sxe->addChild('patient'));
        if (null !== $this->prescriber) $this->prescriber->xmlSerialize(true, $sxe->addChild('prescriber'));
        if (null !== $this->encounter) $this->encounter->xmlSerialize(true, $sxe->addChild('encounter'));
        if (null !== $this->reasonCodeableConcept) $this->reasonCodeableConcept->xmlSerialize(true, $sxe->addChild('reasonCodeableConcept'));
        if (null !== $this->reasonReference) $this->reasonReference->xmlSerialize(true, $sxe->addChild('reasonReference'));
        if (null !== $this->note) $this->note->xmlSerialize(true, $sxe->addChild('note'));
        if (null !== $this->medicationCodeableConcept) $this->medicationCodeableConcept->xmlSerialize(true, $sxe->addChild('medicationCodeableConcept'));
        if (null !== $this->medicationReference) $this->medicationReference->xmlSerialize(true, $sxe->addChild('medicationReference'));
        if (0 < count($this->dosageInstruction)) {
            foreach($this->dosageInstruction as $dosageInstruction) {
                $dosageInstruction->xmlSerialize(true, $sxe->addChild('dosageInstruction'));
            }
        }
        if (null !== $this->dispense) $this->dispense->xmlSerialize(true, $sxe->addChild('dispense'));
        if (null !== $this->substitution) $this->substitution->xmlSerialize(true, $sxe->addChild('substitution'));
        if (null !== $this->priorPrescription) $this->priorPrescription->xmlSerialize(true, $sxe->addChild('priorPrescription'));
        if ($returnSXE) return $sxe;
        return $sxe->saveXML();
    }


}