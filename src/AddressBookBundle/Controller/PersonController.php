<?php

namespace AddressBookBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use AddressBookBundle\Entity\Address;
use AddressBookBundle\Entity\Person;
use AddressBookBundle\Entity\Email;
use AddressBookBundle\Entity\Phone;

class PersonController extends Controller
{
    /**
     * @Route("/")
     * @Method("GET")
     */
    public function showIndexAction()
    {
        $repo = $this->getDoctrine()->getRepository('AddressBookBundle:Person');
        
        $persons = $repo->findOrderBySurname();
        
        return $this->render(
                            'AddressBookBundle:Person:show_index.html.twig', 
                            ['persons' => $persons]);
    }
    /**
     * @Route("/{id}", requirements={"id"="\d+"})
     * @Method("GET")
     */
    public function showPersonAction($id){
        
        $repo = $this->getDoctrine()->getRepository('AddressBookBundle:Person');
        
        $person = $repo->find($id);
        
        if($person == null) {
            throw $this->createNotFoundException();
        }
        
        return $this->render(
                            'AddressBookBundle:Person:show_person.html.twig', 
                            ['person' => $person]);
        
    }
    /**
     * @Route("/add")
     */
    public function addAction(){
        $repo = $this->getDoctrine()->getRepository('AddressBookBundle:Person');
        
        $em = $this->getDoctrine()->getManager();
        
        $person = $repo->find(1);
        
        $address = new Address();
        $address->setCity('Dallas');
        $address->setStreet('Dworcowa');
        $address->setHouseNo(rand(10, 100));
        $address->setFlatNo(rand(10, 100));
        $address->setPerson($person);
        
        
        $person->addAddress($address);
        
        $em->persist($person);
        $em->flush();
        
        return $this->redirectToRoute('addressbook_person_showindex');
        
        
    }
    /**
     * @Route("/{id}/delete", requirements={"id"="\d+"})
     */
    public function deletePersonAction($id){
        $personRepo = $this->getDoctrine()->getRepository('AddressBookBundle:Person');
        $em = $this->getDoctrine()->getManager();
        $person = $personRepo->find($id);
        if($person != null){
            $em->remove($person);
            $em->flush();
        }
        return $this->redirectToRoute("addressbook_person_showindex");
    }
    public function generateForm($person, $action){
        $form = $this->createFormBuilder($person)
                ->setAction($action)
                ->add('name', 'text')
                ->add('surname', 'text')
                ->add('description', 'text')
                ->add('save', 'submit', array('label' => 'Add person'))
                ->getForm();
        return $form;
    }
    public function generateAddressForm($address, $action){
        $form = $this->createFormBuilder($address)
                ->setAction($action)
                ->add('city', 'text')
                ->add('street', 'text')
                ->add('houseNo', 'text')
                ->add('flatNo', 'text')
                ->add('save', 'submit', array('label' => 'Add address'))
                ->getForm();
        return $form;
    }
    public function generateEmailForm($email, $action){
        $form = $this->createFormBuilder($email)
                ->setAction($action)
                ->add('address', 'text')
                ->add('type', 'text')
                ->add('save', 'submit', array('label' => 'Add email'))
                ->getForm();
        return $form;
    }
    public function generatePhoneForm($phone, $action){
        $form = $this->createFormBuilder($phone)
                ->setAction($action)
                ->add('number', 'text')
                ->add('type', 'text')
                ->add('save', 'submit', array('label' => 'Add phone'))
                ->getForm();
        return $form;
    }
    
    /**
     * @Route("/new")
     * @Method("GET")
     */
    public function newPersonAction(){
        $person = new Person();
        $action  = $this->generateUrl('addressbook_person_createperson');
        $formPerson = $this->generateForm($person, $action);
        
        return $this->render('AddressBookBundle:Person:new_person.html.twig', ['form' => $formPerson->createView()]);
    }
    /**
     * @Route("/create")
     * @Method("POST")
     */
    public function createPersonAction(Request $req){
        $person = new Person();
        $action = $this->generateUrl('addressbook_person_createperson');
        $form = $this->generateForm($person, $action);
        $form->handleRequest($req);
        if($form->isSubmitted() && $form->isValid()){
            $person = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($person);
            $em->flush();
        } else {
            return $this->render('AddressBookBundle:Person:new_person.html.twig', ['form' => $form->createView()]);
        }
        return $this->render(
                            'AddressBookBundle:Person:show_person.html.twig', 
                            ['person' => $person]);
        
    }
    /**
     * @Route("/{id}/createAddress", requirements={"id" : "\d+"})
     * @Method("POST")
     */
    public function createAddressAction(Request $req, $id){
        $address = new Address();
        $formAddress = $this->generateAddressForm($address, null);
        $formAddress->handleRequest($req);
        if($formAddress->isSubmitted() && $formAddress->isValid()){
            $repo = $this->getDoctrine()->getRepository('AddressBookBundle:Person');
            $person = $repo->find($id);
            
            $address = $formAddress->getData();
            $address->setPerson($person);
            $person->addAddress($address);
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($address);
            $em->flush();
        }
        return $this->redirectToRoute('addressbook_person_showperson', ['id' => $id]);
    }
    /**
     * @Route("/{id}/createEmail", requirements={"id" : "\d+"})
     * @Method("POST")
     */
    public function createEmailAction(Request $req, $id){
        $email = new Email();
        $formEmail = $this->generateEmailForm($email, null);
        $formEmail->handleRequest($req);
        if($formEmail->isSubmitted()){
            $repo = $this->getDoctrine()->getRepository('AddressBookBundle:Person');
            $person = $repo->find($id);
            
            $email = $formEmail->getData();
            $email->setPerson($person);
            $person->addEmail($email);
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($email);
            $em->flush();
        }
        return $this->redirectToRoute('addressbook_person_showperson', ['id' => $id]);
    }
    /**
     * @Route("/{id}/createPhone", requirements={"id" : "\d+"})
     * @Method("POST")
     */
    public function createPhoneAction(Request $req, $id){
        $phone = new Phone();
        $formPhone = $this->generatePhoneForm($phone, null);
        $formPhone->handleRequest($req);
        if($formPhone->isSubmitted()){
            $repo = $this->getDoctrine()->getRepository('AddressBookBundle:Person');
            $person = $repo->find($id);
            
            $phone = $formPhone->getData();
            $phone->setPerson($person);
            $person->addPhone($phone);
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($phone);
            $em->flush();
        }
        return $this->redirectToRoute('addressbook_person_showperson', ['id' => $id]);
    }

    /**
     * @Route("/{id}/modify", requirements={"id" : "\d+"})
     */
    public function modifyPersonAction(Request $req, $id){
        $personRepo = $this->getDoctrine()->getRepository('AddressBookBundle:Person');
        $person = $personRepo->find($id);
        $action = $this->generateUrl('addressbook_person_modifyperson', ['id' => $person->getId()]);
        $form = $this->generateForm($person, $action);
        $form->handleRequest($req);
        
        if($req->getMethod() == "POST" && $form->isSubmitted() && $form->isValid()){
            $person = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($person);
            $em->flush();
            return $this->render(
                            'AddressBookBundle:Person:show_person.html.twig', 
                            ['person' => $person]);
        }
        //return $this->render('AddressBookBundle:Person:new_person.html.twig', ['form' => $form->createView()]);
        
        $address = new Address();
        $action = $this->generateUrl('addressbook_person_createaddress', ['id' => $id]);
        $formAddress = $this->generateAddressForm($address, $action);
        
        $email = new Email();
        $action = $this->generateUrl('addressbook_person_createemail', ['id' => $id]);
        $formEmail = $this->generateEmailForm($email, $action);
        
        $phone = new Phone();
        $action = $this->generateUrl('addressbook_person_createphone', ['id' => $id]);
        $formPhone = $this->generatePhoneForm($phone, $action);
        
        return $this->render('AddressBookBundle:Person:new_person.html.twig', 
                            ['form' => $form->createView(),
                             'formAddress' => $formAddress->createView(),
                             'formEmail' => $formEmail->createView(),
                             'formPhone' => $formPhone->createView()]);
    }

}
