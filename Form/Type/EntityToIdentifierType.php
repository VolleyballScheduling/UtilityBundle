<?php
namespace ProjectPunchclock\Bundle\CoreBundle\Form\Type;

use \Doctrine\Common\Persistence\ObjectManager;
use \ProjectPunchclock\Bundle\CoreBundle\Form\DataTransformer\EntityToIdentifierTransformer;
use \Symfony\Component\Form\AbstractType;
use \Symfony\Component\Form\FormBuilderInterface;
use \Symfony\Component\OptionsResolver\OptionsResolverInterface;

class EntityToIdentifierType extends AbstractType
{
    /**
     * Object Manager.
     *
     * @var type ObjectManager
     */
    protected $om;

    public function __construct(ObjectManager $om)
    {
        $this->om = $om;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(
            new EntityToIdentifierTransformer($this->om->getRepository($options['class']), $options['identifier'])
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(
                array(
                    'identifier' => 'id'
                )
            )
            ->setAllowedTypes(
                array(
                    'identifier' => array('string')
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'entity';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'project_punchclock_entity_to_identifier';
    }
}
