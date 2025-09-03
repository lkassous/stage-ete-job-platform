<x-mail::message>
# 🤖 Analyse IA de votre CV terminée !

Bonjour **{{ $candidate->prenom }} {{ $candidate->nom }}**,

Bonne nouvelle ! Notre système d'intelligence artificielle a terminé l'analyse de votre CV.

## 📊 Résultats de l'analyse

- **Score de compatibilité :** {{ $score }}%
- **Évaluation globale :** {{ $rating }}
- **Date d'analyse :** {{ $analysisDate }}

@if($analysis->profile_summary)
## 👤 Résumé de profil
{{ $analysis->profile_summary }}
@endif

@if($analysis->key_skills)
## 🎯 Compétences clés identifiées
{{ $analysis->key_skills }}
@endif

@if($analysis->experience_summary)
## 💼 Résumé d'expérience
{{ $analysis->experience_summary }}
@endif

@if($analysis->education_summary)
## 🎓 Formation
{{ $analysis->education_summary }}
@endif

## 🚀 Prochaines étapes

Notre équipe RH va maintenant examiner votre profil en détail. Si votre candidature correspond à nos besoins actuels, nous vous contacterons dans les **5 à 7 jours ouvrables**.

## 📞 Questions ?

Pour toute question concernant votre candidature, contactez-nous à : **rh@cv-filtering.com**

---

Merci pour votre candidature et votre patience !

L'équipe **{{ config('app.name') }}**
</x-mail::message>
