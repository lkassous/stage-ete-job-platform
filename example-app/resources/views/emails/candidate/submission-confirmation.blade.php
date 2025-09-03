<x-mail::message>
# 🎉 Candidature reçue avec succès !

Bonjour **{{ $candidate->prenom }} {{ $candidate->nom }}**,

Nous avons bien reçu votre candidature le **{{ $submissionDate }}**.

## 📋 Récapitulatif de votre candidature

- **Nom complet :** {{ $candidate->prenom }} {{ $candidate->nom }}
- **Email :** {{ $candidate->email }}
- **Téléphone :** {{ $candidate->telephone }}
@if($candidate->linkedin_url)
- **LinkedIn :** [Voir le profil]({{ $candidate->linkedin_url }})
@endif
- **Date de soumission :** {{ $submissionDate }}

## 🤖 Prochaines étapes

1. **Analyse automatique :** Notre système IA va analyser votre CV dans les prochaines minutes
2. **Notification :** Vous recevrez un email dès que l'analyse sera terminée
3. **Examen RH :** Notre équipe RH examinera votre profil
4. **Contact :** Nous vous contacterons si votre profil correspond à nos besoins

## 📞 Besoin d'aide ?

Si vous avez des questions, n'hésitez pas à nous contacter à l'adresse : **support@cv-filtering.com**

---

Merci pour votre intérêt pour notre entreprise ! 🚀

L'équipe **{{ config('app.name') }}**
</x-mail::message>
